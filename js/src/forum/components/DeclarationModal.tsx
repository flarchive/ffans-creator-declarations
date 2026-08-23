import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import Form from 'flarum/common/components/Form';
import FormModal, { IFormModalAttrs } from 'flarum/common/components/FormModal';
import Icon from 'flarum/common/components/Icon';
import type Mithril from 'mithril';
import {
  DeclarationDefinition,
  DeclarationSelection,
  enabledDefinitions,
  labelFor,
} from '../utils/declarations';

interface DeclarationModalAttrs extends IFormModalAttrs {
  selected: DeclarationSelection[];
  showDisabled?: boolean;
  onsave: (selected: DeclarationSelection[]) => void | Promise<void>;
}

export default class DeclarationModal extends FormModal<DeclarationModalAttrs> {
  selected: DeclarationSelection[] = [];
  preservedKeys: string[] = [];

  oninit(vnode: Mithril.Vnode<DeclarationModalAttrs, this>) {
    super.oninit(vnode);
    this.selected = this.attrs.selected.map((item) => ({ ...item }));
    this.preservedKeys = this.attrs.showDisabled
      ? this.attrs.selected.map((item) => item.key)
      : [];
  }

  className() {
    return 'CreatorDeclarationsModal';
  }

  title() {
    return app.translator.trans('ffans-creator-declarations.forum.modal.title');
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
    const groups: DeclarationDefinition['category'][] = [
      'source',
      'authenticity',
      'safety',
      'commercial',
    ];
    const max = app.forum.attribute<number>('creatorDeclarationsMax') || 5;

    return (
      <div
        className="Modal-body"
        ontouchstart={(event: TouchEvent) => event.stopPropagation()}
        ontouchmove={(event: TouchEvent) => event.stopPropagation()}
      >
        <Form>
          <p className="helpText">
            {app.translator.trans(
              'ffans-creator-declarations.forum.modal.help',
            )}
          </p>
          {groups.map((category) => {
            const items = enabledDefinitions(this.preservedKeys).filter(
              (definition) => definition.category === category,
            );
            if (!items.length) return null;

            return (
              <fieldset className="CreatorDeclarationsModal-group">
                <legend>
                  {app.translator.trans(
                    `ffans-creator-declarations.lib.categories.${category}`,
                  )}
                </legend>
                {items.map((definition) => this.declarationField(definition))}
              </fieldset>
            );
          })}

          <div className="Form-group Form-controls CreatorDeclarationsModal-actions">
            <Button
              className="Button Button--primary"
              type="submit"
              loading={this.loading}
            >
              {app.translator.trans(
                'ffans-creator-declarations.forum.modal.save_button',
              )}
            </Button>
            <span className="CreatorDeclarationsModal-selectionCount">
              {app.translator.trans(
                'ffans-creator-declarations.forum.modal.selection_count',
                { selected: this.selected.length, max },
              )}
            </span>
          </div>
        </Form>
      </div>
    );
  }

  declarationField(definition: DeclarationDefinition) {
    const selection = this.selected.find((item) => item.key === definition.key);
    const max = app.forum.attribute<number>('creatorDeclarationsMax') || 5;
    const disabled = !selection && this.selected.length >= max;
    const fieldClassName = [
      'CreatorDeclarationsModal-field',
      selection ? 'is-selected' : '',
      disabled ? 'is-disabled' : '',
    ]
      .filter(Boolean)
      .join(' ');

    return (
      <div className={fieldClassName}>
        <button
          className="CreatorDeclarationsModal-option"
          type="button"
          disabled={disabled}
          aria-pressed={!!selection}
          onclick={() => this.toggle(definition, !selection)}
        >
          <span className="CreatorDeclarationsModal-label">
            <Icon name={definition.icon} style={{ color: definition.color }} />
            <span>
              <strong>{labelFor(definition.key)}</strong>
              <small>
                {app.translator.trans(
                  `ffans-creator-declarations.lib.declarations.${definition.key}.help`,
                )}
              </small>
            </span>
          </span>
          <span className="CreatorDeclarationsModal-check" aria-hidden="true">
            <Icon name="fas fa-check" />
          </span>
        </button>

        {selection && definition.detailsType && (
          <textarea
            className="FormControl CreatorDeclarationsModal-details"
            rows={1}
            value={selection.details}
            required={definition.key === 'repost'}
            maxlength={500}
            placeholder={
              app.translator.trans(
                `ffans-creator-declarations.lib.declarations.${definition.key}.details_placeholder`,
                {},
                true,
              ) as string
            }
            oninput={(event: InputEvent) => {
              selection.details = (event.target as HTMLTextAreaElement).value;
            }}
          />
        )}
      </div>
    );
  }

  toggle(definition: DeclarationDefinition, checked: boolean) {
    if (checked) {
      if (definition.key === 'original')
        this.selected = this.selected.filter((item) => item.key !== 'repost');
      if (definition.key === 'repost')
        this.selected = this.selected.filter((item) => item.key !== 'original');
      this.selected.push({ key: definition.key, details: '' });
    } else {
      this.selected = this.selected.filter(
        (item) => item.key !== definition.key,
      );
    }
  }

  async onsubmit(event: SubmitEvent) {
    event.preventDefault();
    this.loading = true;

    try {
      await this.attrs.onsave(this.selected.map((item) => ({ ...item })));
      this.hide();
    } catch (_) {
      this.loaded();
    }
  }
}
