import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import icon from 'flarum/common/helpers/icon';
import extractText from 'flarum/common/utils/extractText';
import CreatorDeclaration from '../../common/models/CreatorDeclaration';
import DeclarationInfoModal from './DeclarationInfoModal';
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
        return extractText(
          app.translator.trans(
            `ffans-creator-declarations.forum.display.${key}_summary`,
          ),
        );
      }

      return extractText(
        app.translator.trans(
          `ffans-creator-declarations.lib.declarations.${declaration.key()}.label`,
        ),
      );
    });

    return (
      <section className="CreatorDeclarations">
        <button
          type="button"
          className="CreatorDeclarations-summary"
          aria-label={extractText(
            app.translator.trans(
              'ffans-creator-declarations.forum.display.open_details',
            ),
          )}
          onclick={() => {
            app.modal.show(DeclarationInfoModal, {
              declarations: validDeclarations,
            });
          }}
        >
          {icon('fas fa-bullhorn', { className: 'CreatorDeclarations-icon' })}
          <span className="CreatorDeclarations-format">
            {app.translator.trans(
              'ffans-creator-declarations.forum.display.declaration_format',
              {
                declarations: labels.join(
                  extractText(
                    app.translator.trans(
                      'ffans-creator-declarations.forum.display.declaration_separator',
                    ),
                  ),
                ),
              },
            )}
          </span>
          <span className="CreatorDeclarations-more" aria-hidden="true">
            {icon('fas fa-chevron-right')}
          </span>
        </button>
      </section>
    );
  }
}
