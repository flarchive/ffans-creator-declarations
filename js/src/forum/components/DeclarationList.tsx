import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import Icon from 'flarum/common/components/Icon';
import CreatorDeclaration from '../../common/models/CreatorDeclaration';
import {
  definitionFor,
  isSourceDeclarationKey,
  sortDeclarations,
} from '../utils/declarations';
import type Mithril from 'mithril';

interface DeclarationListAttrs {
  declarations: CreatorDeclaration[];
}

export default class DeclarationList extends Component<DeclarationListAttrs> {
  view(vnode: Mithril.Vnode<DeclarationListAttrs, this>) {
    const validDeclarations = sortDeclarations(
      vnode.attrs.declarations.filter((declaration) =>
        definitionFor(declaration.key()),
      ),
    );
    const hasOtherDeclarations = validDeclarations.some(
      (declaration) => !isSourceDeclarationKey(declaration.key()),
    );

    if (!hasOtherDeclarations) return null;

    const labels = validDeclarations.map((declaration) => {
      const key = declaration.key();

      if (['original', 'reference', 'personal_opinion'].includes(key)) {
        return app.translator.trans(
          `ffans-creator-declarations.forum.display.${key}_summary`,
          {},
          true,
        ) as unknown as string;
      }

      return app.translator.trans(
        `ffans-creator-declarations.lib.declarations.${declaration.key()}.label`,
        {},
        true,
      ) as unknown as string;
    });

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
          <span className="CreatorDeclarations-format">
            {app.translator.trans(
              'ffans-creator-declarations.forum.display.declaration_format',
              {
                declarations: labels.join(
                  app.translator.trans(
                    'ffans-creator-declarations.forum.display.declaration_separator',
                    {},
                    true,
                  ) as unknown as string,
                ),
              },
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
