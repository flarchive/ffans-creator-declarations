import app from 'flarum/admin/app';
import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import SettingDropdown from 'flarum/admin/components/SettingDropdown';
import LinkButton from 'flarum/common/components/LinkButton';
import { extend } from 'flarum/common/extend';

const declarationCategories = [
  {
    key: 'source',
    declarations: ['original', 'repost', 'reference'],
  },
  {
    key: 'authenticity',
    declarations: ['ai_generated', 'fictional', 'personal_opinion'],
  },
  {
    key: 'safety',
    declarations: ['professional', 'sensitive'],
  },
  {
    key: 'commercial',
    declarations: ['self_promotion', 'sponsored'],
  },
];

app.initializers.add('ffans-creator-declarations-admin', () => {
  extend(
    ExtensionPage.prototype,
    'infoItems',
    function (this: any, items: any) {
      if (this.extension.id !== 'ffans-creator-declarations') return;
      if (!app.data.locale.startsWith('zh')) return;

      const forumZh = this.extension.links.forumZh;

      if (!forumZh) return;

      let priority = 0;

      if (items.has('discuss')) {
        const itemNames = Object.keys(items.toObject());

        itemNames.forEach((itemName, index) => {
          items.setPriority(itemName, itemNames.length - index);
        });

        priority = items.getPriority('discuss') - 0.5;
      }

      items.add(
        'forumZh',
        <LinkButton
          href={forumZh}
          icon="fas fa-comments"
          external={true}
          target="_blank"
        >
          中文社区
        </LinkButton>,
        priority,
      );
    },
  );

  const allowEditOwnPermission = {
    permission: 'ffans-creator-declarations.allow_edit_own',
    icon: 'fas fa-user-edit',
    label: app.translator.trans(
      'ffans-creator-declarations.admin.permissions.allow_edit_own_label',
    ),
    setting: () => {
      const minutes = parseInt(
        app.data.settings['ffans-creator-declarations.allow_edit_own'],
        10,
      );

      return (
        <SettingDropdown
          defaultLabel={
            minutes
              ? app.translator.trans(
                  'core.admin.permissions_controls.allow_some_minutes_button',
                  { count: minutes },
                )
              : app.translator.trans(
                  'core.admin.permissions_controls.allow_indefinitely_button',
                )
          }
          key="ffans-creator-declarations.allow_edit_own"
          options={[
            {
              value: '-1',
              label: app.translator.trans(
                'core.admin.permissions_controls.allow_indefinitely_button',
              ),
            },
            {
              value: '10',
              label: app.translator.trans(
                'core.admin.permissions_controls.allow_ten_minutes_button',
              ),
            },
            {
              value: 'reply',
              label: app.translator.trans(
                'core.admin.permissions_controls.allow_until_reply_button',
              ),
            },
          ]}
        />
      );
    },
  };

  app.extensionData
    .for('ffans-creator-declarations')
    .registerSetting(
      {
        setting: 'ffans-creator-declarations.required_discussion',
        type: 'boolean',
        label: app.translator.trans(
          'ffans-creator-declarations.admin.settings.required_discussion_label',
        ),
        help: app.translator.trans(
          'ffans-creator-declarations.admin.settings.required_discussion_help',
        ),
      },
      100,
    )
    .registerSetting(
      {
        setting: 'ffans-creator-declarations.required_reply',
        type: 'boolean',
        label: app.translator.trans(
          'ffans-creator-declarations.admin.settings.required_reply_label',
        ),
        help: app.translator.trans(
          'ffans-creator-declarations.admin.settings.required_reply_help',
        ),
      },
      95,
    )
    .registerSetting(
      {
        setting: 'ffans-creator-declarations.max',
        type: 'number',
        min: 1,
        max: 9,
        label: app.translator.trans(
          'ffans-creator-declarations.admin.settings.max_label',
        ),
      },
      90,
    )
    .registerSetting(
      {
        setting: 'ffans-creator-declarations.show_in_user_post_lists',
        type: 'boolean',
        label: app.translator.trans(
          'ffans-creator-declarations.admin.settings.show_in_user_post_lists_label',
        ),
        help: app.translator.trans(
          'ffans-creator-declarations.admin.settings.show_in_user_post_lists_help',
        ),
      },
      82,
    )
    .registerSetting(
      {
        setting: 'ffans-creator-declarations.order',
        type: 'text',
        label: app.translator.trans(
          'ffans-creator-declarations.admin.settings.order_label',
        ),
        help: app.translator.trans(
          'ffans-creator-declarations.admin.settings.order_help',
        ),
      },
      80,
    )
    .registerSetting(function (this: any) {
      return (
        <div className="FormSection CreatorDeclarationsAdmin-section">
          <div className="FormSection-body">
            {declarationCategories.map((category) => [
              <h3 className="CreatorDeclarationsAdmin-category">
                {app.translator.trans(
                  'ffans-creator-declarations.lib.categories.' + category.key,
                )}
              </h3>,
              ...category.declarations.map((key) =>
                this.buildSettingComponent({
                  setting: 'ffans-creator-declarations.enabled.' + key,
                  type: 'boolean',
                  label: app.translator.trans(
                    'ffans-creator-declarations.lib.declarations.' +
                      key +
                      '.label',
                  ),
                  help: app.translator.trans(
                    'ffans-creator-declarations.lib.declarations.' +
                      key +
                      '.help',
                  ),
                }),
              ),
            ])}
          </div>
        </div>
      );
    }, 70)
    .registerPermission(allowEditOwnPermission, 'reply', 90)
    .registerPermission(
      {
        icon: 'fas fa-file-signature',
        label: app.translator.trans(
          'ffans-creator-declarations.admin.permissions.moderate_label',
        ),
        permission: 'discussion.moderateCreatorDeclarations',
      },
      'moderate',
      60,
    );
});
