import app from 'flarum/forum/app';
import CreatorDeclaration from '../../common/models/CreatorDeclaration';

export interface DeclarationDefinition {
  key: string;
  icon: string;
  color: string;
  category: 'source' | 'authenticity' | 'safety' | 'commercial';
  attribute: string;
  detailsType?: 'url' | 'text';
}

export interface DeclarationSelection {
  key: string;
  details: string;
}

export const definitions: DeclarationDefinition[] = [
  {
    key: 'original',
    icon: 'fas fa-certificate',
    color: '#2f855a',
    category: 'source',
    attribute: 'creatorDeclarationOriginalEnabled',
  },
  {
    key: 'repost',
    icon: 'fas fa-link',
    color: '#64748b',
    category: 'source',
    attribute: 'creatorDeclarationRepostEnabled',
    detailsType: 'url',
  },
  {
    key: 'ai_generated',
    icon: 'fas fa-wand-magic-sparkles',
    color: '#6557d2',
    category: 'authenticity',
    attribute: 'creatorDeclarationAiGeneratedEnabled',
    detailsType: 'text',
  },
  {
    key: 'fictional',
    icon: 'fas fa-masks-theater',
    color: '#7c3aed',
    category: 'authenticity',
    attribute: 'creatorDeclarationFictionalEnabled',
  },
  {
    key: 'personal_opinion',
    icon: 'fas fa-comment-dots',
    color: '#2563eb',
    category: 'authenticity',
    attribute: 'creatorDeclarationPersonalOpinionEnabled',
  },
  {
    key: 'professional',
    icon: 'fas fa-triangle-exclamation',
    color: '#d97706',
    category: 'safety',
    attribute: 'creatorDeclarationProfessionalEnabled',
    detailsType: 'text',
  },
  {
    key: 'sensitive',
    icon: 'fas fa-eye-slash',
    color: '#ea580c',
    category: 'safety',
    attribute: 'creatorDeclarationSensitiveEnabled',
    detailsType: 'text',
  },
  {
    key: 'self_promotion',
    icon: 'fas fa-bullhorn',
    color: '#0891b2',
    category: 'commercial',
    attribute: 'creatorDeclarationSelfPromotionEnabled',
  },
  {
    key: 'sponsored',
    icon: 'fas fa-handshake',
    color: '#0f766e',
    category: 'commercial',
    attribute: 'creatorDeclarationSponsoredEnabled',
    detailsType: 'text',
  },
];

function configuredOrder(): string[] {
  return String(app.forum.attribute<string>('creatorDeclarationOrder') || '')
    .split(',')
    .map((key) => key.trim());
}

function orderIndex(key: string, order: string[]): number {
  const configuredIndex = order.indexOf(key);
  if (configuredIndex !== -1) return configuredIndex;

  const definitionIndex = definitions.findIndex(
    (definition) => definition.key === key,
  );
  return (
    order.length +
    (definitionIndex === -1 ? definitions.length : definitionIndex)
  );
}

export function enabledDefinitions(
  preservedKeys: string[] = [],
): DeclarationDefinition[] {
  const order = configuredOrder();

  return definitions
    .filter(
      (definition) =>
        app.forum.attribute<boolean>(definition.attribute) !== false ||
        preservedKeys.includes(definition.key),
    )
    .sort((a, b) => orderIndex(a.key, order) - orderIndex(b.key, order));
}

export function sortDeclarations(
  models: CreatorDeclaration[],
): CreatorDeclaration[] {
  const order = configuredOrder();
  return [...models].sort(
    (a, b) => orderIndex(a.key(), order) - orderIndex(b.key(), order),
  );
}

export function definitionFor(key: string): DeclarationDefinition | undefined {
  return definitions.find((definition) => definition.key === key);
}

export function labelFor(key: string) {
  return app.translator.trans(
    `ffans-creator-declarations.lib.declarations.${key}.label`,
  );
}

export function selectionsFromModels(
  models: CreatorDeclaration[],
): DeclarationSelection[] {
  return models.map((model) => ({
    key: model.key(),
    details: model.metadata()?.details || '',
  }));
}
