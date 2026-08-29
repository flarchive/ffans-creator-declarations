import app from 'flarum/forum/app';
import icon from 'flarum/common/helpers/icon';
import extractText from 'flarum/common/utils/extractText';
import Modal, {
  type IInternalModalAttrs,
} from 'flarum/common/components/Modal';
import CreatorDeclaration from '../../common/models/CreatorDeclaration';
import { definitionFor, labelFor } from '../utils/declarations';

interface DeclarationInfoModalAttrs extends IInternalModalAttrs {
  declarations: CreatorDeclaration[];
}

export default class DeclarationInfoModal extends Modal<DeclarationInfoModalAttrs> {
  className() {
    return 'CreatorDeclarationInfoModal';
  }

  title() {
    return app.translator.trans(
      'ffans-creator-declarations.forum.info_modal.title',
    );
  }

  onready(): void {
    const activeElement = document.activeElement;

    if (
      activeElement instanceof HTMLElement &&
      this.$()[0]?.contains(activeElement)
    ) {
      activeElement.blur();
    }
  }

  content() {
    const declarations = this.attrs.declarations.filter((declaration) =>
      definitionFor(declaration.key()),
    );
    const labels = declarations.map((declaration) => {
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
          `ffans-creator-declarations.lib.declarations.${key}.label`,
        ),
      );
    });

    return (
      <div
        className="Modal-body"
        ontouchstart={(event: TouchEvent) => event.stopPropagation()}
        ontouchmove={(event: TouchEvent) => event.stopPropagation()}
      >
        <div className="CreatorDeclarationInfoModal-content">
          <div className="CreatorDeclarationInfoModal-intro">
            {icon('fas fa-info')}
            <h3>
              {app.translator.trans(
                'ffans-creator-declarations.forum.info_modal.heading',
              )}
            </h3>
            <p>
              {app.translator.trans(
                'ffans-creator-declarations.forum.info_modal.explanation',
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
            </p>
            <p>
              {app.translator.trans(
                'ffans-creator-declarations.forum.info_modal.disclaimer',
              )}
            </p>
          </div>

          <div className="CreatorDeclarationInfoModal-list">
            {declarations.map((declaration) => {
              const definition = definitionFor(declaration.key())!;
              const details = declaration.metadata()?.details;
              const title = declaration.metadata()?.title;

              return (
                <div
                  className="CreatorDeclarationInfoModal-item"
                  key={declaration.id()}
                >
                  {icon(definition.icon, {
                    style: { color: definition.color },
                  })}
                  <div>
                    <strong>{labelFor(definition.key)}</strong>
                    <p>
                      {app.translator.trans(
                        `ffans-creator-declarations.lib.declarations.${definition.key}.help`,
                      )}
                    </p>
                    {details && definition.detailsType === 'url' && (
                      <div className="CreatorDeclarationInfoModal-note">
                        <span>
                          {app.translator.trans(
                            `ffans-creator-declarations.forum.info_modal.${declaration.key() === 'reference' ? 'reference' : 'source'}`,
                          )}
                        </span>
                        <a
                          href={details}
                          target="_blank"
                          rel="noopener noreferrer nofollow ugc"
                        >
                          {title || details}
                        </a>
                      </div>
                    )}
                    {details && definition.detailsType !== 'url' && (
                      <div className="CreatorDeclarationInfoModal-note">
                        <span>
                          {app.translator.trans(
                            'ffans-creator-declarations.forum.info_modal.author_note',
                          )}
                        </span>
                        {details}
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        </div>
      </div>
    );
  }
}
