import Model from 'flarum/common/Model';

export default class CreatorDeclaration extends Model {
  key = Model.attribute<string>('key');
  source = Model.attribute<string>('source');
  metadata = Model.attribute<{ details?: string } | null>('metadata');
  createdAt = Model.attribute<Date | null, string | null>(
    'createdAt',
    Model.transformDate,
  );
}
