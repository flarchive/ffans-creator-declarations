import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Icon from 'flarum/common/components/Icon';
import CreatorDeclaration from '../../common/models/CreatorDeclaration';
import { definitionFor, sortDeclarations } from '../utils/declarations';
import type Mithril from 'mithril';

interface DeclarationListAttrs {
  declarations: CreatorDeclaration[];
}

export default class DeclarationList extends Component<DeclarationListAttrs> {
  view(vnode: Mithril.Vnode<DeclarationListAttrs, this>) {
    const showOriginalInPostHeader = app.forum.attribute<boolean>(
      'creatorDeclarationShowOriginalInPostHeader',
    );
    const validDeclarations = sortDeclarations(
      vnode.attrs.declarations.filter((declaration) =>
        definitionFor(declaration.key()),
      ),
    );
    const declarations = validDeclarations.filter(
      (declaration) =>
        !(
          showOriginalInPostHeader &&
          ['original', 'repost'].includes(declaration.key())
        ),
    );
    if (!declarations.length) return null;

    const labels = declarations.map(
      (declaration) =>
        app.translator.trans(
          `ffans-creator-declarations.lib.declarations.${declaration.key()}.label`,
          {},
          true,
        ) as unknown as string,
    );

    return (
      <section className="CreatorDeclarations">
        <button
          type="button"
          className="CreatorDeclarations-summary"
          aria-label={
            app.translator.trans(
              'ffans-creator-declarations.forum.display.open_details',
              {},
              true,
            ) as unknown as string
          }
          onclick={() => {
            app.modal.show(() => import('./DeclarationInfoModal'), {
              declarations: validDeclarations,
            });
          }}
        >
          <Icon name="fas fa-bullhorn" className="CreatorDeclarations-icon" />
          <span className="CreatorDeclarations-prefix">
            {app.translator.trans(
              'ffans-creator-declarations.forum.display.prefix',
            )}
          </span>
          <span className="CreatorDeclarations-labels">
            {labels.join(
              app.translator.trans(
                'ffans-creator-declarations.forum.display.declaration_separator',
                {},
                true,
              ) as unknown as string,
            )}
          </span>
          <span className="CreatorDeclarations-more" aria-hidden="true">
            <Icon name="fas fa-chevron-right" />
          </span>
        </button>
      </section>
    );
  }
}
