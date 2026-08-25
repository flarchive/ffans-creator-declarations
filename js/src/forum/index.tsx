import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import Model from 'flarum/common/Model';
import { extend as extendComponent, override } from 'flarum/common/extend';
import Post from 'flarum/common/models/Post';
import CommentPost from 'flarum/forum/components/CommentPost';
import Composer from 'flarum/forum/components/Composer';
import DiscussionComposer from 'flarum/forum/components/DiscussionComposer';
import PostsUserPage from 'flarum/forum/components/PostsUserPage';
import ReplyComposer from 'flarum/forum/components/ReplyComposer';
import PostControls from 'flarum/forum/utils/PostControls';
import CreatorDeclaration from '../common/models/CreatorDeclaration';
import DeclarationModal from './components/DeclarationModal';
import DeclarationList from './components/DeclarationList';
import SourceDeclaration from './components/SourceDeclaration';
import {
  DeclarationSelection,
  selectionsFromModels,
} from './utils/declarations';

function initializeDeclarationComposer(composerBody: any) {
  composerBody.composer.fields.creatorDeclarations =
    composerBody.composer.fields.creatorDeclarations || [];

  composerBody.chooseCreatorDeclarations = function () {
    app.modal.show(DeclarationModal, {
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
  app.store.models['creator-declarations'] = CreatorDeclaration;
  (Post.prototype as any).creatorDeclarations =
    Model.hasMany<CreatorDeclaration>('creatorDeclarations');
  (Post.prototype as any).canEditCreatorDeclarations = Model.attribute<boolean>(
    'canEditCreatorDeclarations',
  );

  extendComponent(DiscussionComposer.prototype, 'oninit', function (this: any) {
    initializeDeclarationComposer(this);
  });

  extendComponent(ReplyComposer.prototype, 'oninit', function (this: any) {
    initializeDeclarationComposer(this);
  });

  override(
    Composer.prototype,
    'focus',
    function (this: any, original: () => void) {
      const composerBody = this.state.body.componentClass;

      if (composerBody === DiscussionComposer) {
        const titleInput = this.$(
          '.ComposerBody--discussion .item-discussionTitle input:enabled:visible',
        ).first();

        if (titleInput.length) {
          titleInput.focus();
          return;
        }
      }

      if (composerBody === ReplyComposer) {
        const editor = this.$(
          '.ComposerBody-editor :input:enabled:visible, .ComposerBody-editor .TextEditor-editor:visible',
        ).first();

        if (editor.length) {
          editor.focus();
          return;
        }
      }

      original();
    },
  );

  extendComponent(
    DiscussionComposer.prototype,
    'headerItems',
    function (this: any, items: any) {
      addComposerDeclarationButton(this, items);
    },
  );

  extendComponent(
    ReplyComposer.prototype,
    'headerItems',
    function (this: any, items: any) {
      addComposerDeclarationButton(this, items);
    },
  );

  extendComponent(
    DiscussionComposer.prototype,
    'data',
    function (this: any, data: any) {
      data.creatorDeclarationData =
        this.composer.fields.creatorDeclarations || [];
    },
  );

  extendComponent(
    ReplyComposer.prototype,
    'data',
    function (this: any, data: any) {
      data.creatorDeclarationData =
        this.composer.fields.creatorDeclarations || [];
    },
  );

  override(
    DiscussionComposer.prototype,
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
    ReplyComposer.prototype,
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
    CommentPost.prototype,
    'bodyItems',
    function (this: any, items: any) {
      if (!shouldShowCreatorDeclarations()) {
        return;
      }

      const declarations = getCreatorDeclarations(this.attrs.post);

      if (declarations.length) {
        items.add(
          'creatorDeclarations',
          <>
            <DeclarationList declarations={declarations} />
            <SourceDeclaration declarations={declarations} />
          </>,
          110,
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

            app.modal.show(DeclarationModal, {
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
