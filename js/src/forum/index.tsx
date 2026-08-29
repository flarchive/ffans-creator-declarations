import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import Extend from 'flarum/common/extenders';
import { extend as extendComponent, override } from 'flarum/common/extend';
import Post from 'flarum/common/models/Post';
import PostsUserPage from 'flarum/forum/components/PostsUserPage';
import PostControls from 'flarum/forum/utils/PostControls';
import CreatorDeclaration from '../common/models/CreatorDeclaration';
import commonExtend from '../common/extend';
import DeclarationList from './components/DeclarationList';
import SourceDeclaration from './components/SourceDeclaration';
import {
  DeclarationSelection,
  selectionsFromModels,
} from './utils/declarations';

export const extend = [
  ...commonExtend,
  new Extend.Model(Post)
    .hasMany<CreatorDeclaration>('creatorDeclarations')
    .attribute<boolean>('canEditCreatorDeclarations'),
];

function initializeDeclarationComposer(composerBody: any) {
  composerBody.composer.fields.creatorDeclarations =
    composerBody.composer.fields.creatorDeclarations || [];

  composerBody.chooseCreatorDeclarations = function () {
    app.modal.show(() => import('./components/DeclarationModal'), {
      selected: this.composer.fields.creatorDeclarations.map(
        (item: DeclarationSelection) => ({ ...item }),
      ),
      onsave: (selected: DeclarationSelection[]) => {
        this.composer.fields.creatorDeclarations = selected;
        m.redraw();
      },
    });
  };
}

function addComposerDeclarationButton(composerBody: any, items: any) {
  const selected: DeclarationSelection[] =
    composerBody.composer.fields.creatorDeclarations || [];

  items.add(
    'creatorDeclarations',
    <Button
      className="Button Button--link Composer-creatorDeclarations"
      icon="fas fa-file-signature"
      onclick={composerBody.chooseCreatorDeclarations.bind(composerBody)}
    >
      {selected.length
        ? app.translator.trans(
            'ffans-creator-declarations.forum.composer.selected',
            { count: selected.length },
          )
        : app.translator.trans(
            'ffans-creator-declarations.forum.composer.choose',
          )}
    </Button>,
    5,
  );
}

function getCreatorDeclarations(post: Post): CreatorDeclaration[] {
  const declarations = (post as any).creatorDeclarations();

  return Array.isArray(declarations) ? declarations : [];
}
async function loadCreatorDeclarations(
  post: Post,
): Promise<CreatorDeclaration[]> {
  if (!Array.isArray((post as any).creatorDeclarations())) {
    await app.store.find<Post>('posts', post.id()!, {
      include: 'creatorDeclarations',
    });
  }

  return getCreatorDeclarations(post);
}

function shouldShowCreatorDeclarations(): boolean {
  return (
    !app.current.matches(PostsUserPage) ||
    app.forum.attribute<boolean>('creatorDeclarationShowInUserPostLists') ===
      true
  );
}

app.initializers.add('ffans-creator-declarations', () => {
  extendComponent(
    'flarum/forum/states/PostListState',
    'requestParams',
    function (params: any) {
      if (
        params.filter?.author &&
        !app.forum.attribute<boolean>('creatorDeclarationShowInUserPostLists')
      ) {
        return;
      }

      params.include ||= [];

      if (!params.include.includes('creatorDeclarations')) {
        params.include.push('creatorDeclarations');
      }
    },
  );

  extendComponent(
    'flarum/forum/components/DiscussionComposer',
    'oninit',
    function (this: any) {
      initializeDeclarationComposer(this);
    },
  );

  extendComponent(
    'flarum/forum/components/ReplyComposer',
    'oninit',
    function (this: any) {
      if (!this.constructor.focusOnSelector) {
        this.constructor.focusOnSelector = () =>
          '.ComposerBody-editor :input:enabled:visible, .ComposerBody-editor .TextEditor-editor';
      }

      initializeDeclarationComposer(this);
    },
  );

  extendComponent(
    'flarum/forum/components/DiscussionComposer',
    'headerItems',
    function (this: any, items: any) {
      addComposerDeclarationButton(this, items);
    },
  );

  extendComponent(
    'flarum/forum/components/ReplyComposer',
    'headerItems',
    function (this: any, items: any) {
      addComposerDeclarationButton(this, items);
    },
  );

  extendComponent(
    'flarum/forum/components/DiscussionComposer',
    'data',
    function (this: any, data: any) {
      data.creatorDeclarationData =
        this.composer.fields.creatorDeclarations || [];
    },
  );

  extendComponent(
    'flarum/forum/components/ReplyComposer',
    'data',
    function (this: any, data: any) {
      data.creatorDeclarationData =
        this.composer.fields.creatorDeclarations || [];
    },
  );

  override(
    'flarum/forum/components/DiscussionComposer',
    'onsubmit',
    function (this: any, original: () => void) {
      const selected: DeclarationSelection[] =
        this.composer.fields.creatorDeclarations || [];

      if (
        app.forum.attribute<boolean>(
          'creatorDeclarationsRequiredForDiscussion',
        ) &&
        !selected.length
      ) {
        this.chooseCreatorDeclarations();
        return;
      }

      original();
    },
  );

  override(
    'flarum/forum/components/ReplyComposer',
    'onsubmit',
    function (this: any, original: () => void) {
      const selected: DeclarationSelection[] =
        this.composer.fields.creatorDeclarations || [];

      if (
        app.forum.attribute<boolean>('creatorDeclarationsRequiredForReply') &&
        !selected.length
      ) {
        this.chooseCreatorDeclarations();
        return;
      }

      original();
    },
  );

  extendComponent(
    'flarum/forum/components/CommentPost',
    'content',
    function (this: any, content: any[]) {
      if (!shouldShowCreatorDeclarations()) {
        return;
      }

      const declarations = getCreatorDeclarations(this.attrs.post);
      const comment = content.find(
        (item: any) => item?.attrs?.contentHtml !== undefined,
      );

      if (declarations?.length && comment) {
        comment.attrs.creatorDeclarations = declarations;
      }
    },
  );

  extendComponent(
    'flarum/forum/components/Comment',
    'view',
    function (this: any, content: any[]) {
      const declarations = this.attrs
        .creatorDeclarations as CreatorDeclaration[];
      const bodyIndex = content.findIndex(
        (item: any) => item?.attrs?.className === 'Post-body',
      );

      if (declarations?.length && bodyIndex !== -1) {
        content.splice(
          bodyIndex,
          0,
          <DeclarationList declarations={declarations} />,
          <SourceDeclaration declarations={declarations} />,
        );
      }
    },
  );

  extendComponent(
    PostControls,
    'moderationControls',
    function (items: any, post: Post) {
      if (
        post.contentType() !== 'comment' ||
        !(post as any).canEditCreatorDeclarations()
      )
        return;

      items.add(
        'creatorDeclarations',
        <Button
          icon="fas fa-bullhorn"
          onclick={async () => {
            const selected = selectionsFromModels(
              await loadCreatorDeclarations(post),
            );

            app.modal.show(() => import('./components/DeclarationModal'), {
              selected,
              showDisabled: !!app.session.user?.isAdmin(),
              onsave: (next: DeclarationSelection[]) =>
                (post as any).save({ creatorDeclarationData: next }),
            });
          }}
        >
          {app.translator.trans(
            'ffans-creator-declarations.forum.controls.edit',
          )}
        </Button>,
      );
    },
  );
});
