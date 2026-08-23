import app from 'flarum/forum/app';
import Icon from 'flarum/common/components/Icon';
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
    const labels = declarations.map(
      (declaration) =>
        app.translator.trans(
          `ffans-creator-declarations.lib.declarations.${declaration.key()}.label`,
          {},
          true,
        ) as unknown as string,
    );

    return (
      <div
        className="Modal-body"
        ontouchstart={(event: TouchEvent) => event.stopPropagation()}
        ontouchmove={(event: TouchEvent) => event.stopPropagation()}
      >
        <div className="CreatorDeclarationInfoModal-content">
          <div className="CreatorDeclarationInfoModal-intro">
            <Icon name="fas fa-info" />
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
                    app.translator.trans(
                      'ffans-creator-declarations.forum.display.declaration_separator',
                      {},
                      true,
                    ) as unknown as string,
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

              return (
                <div
                  className="CreatorDeclarationInfoModal-item"
                  key={declaration.id()}
                >
                  <Icon
                    name={definition.icon}
                    style={{ color: definition.color }}
                  />
                  <div>
                    <strong>{labelFor(definition.key)}</strong>
                    <p>
                      {app.translator.trans(
                        `ffans-creator-declarations.lib.declarations.${definition.key}.help`,
                      )}
                    </p>
                    {details && (
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
