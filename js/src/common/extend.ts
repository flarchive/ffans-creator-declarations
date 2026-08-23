import Extend from 'flarum/common/extenders';
import CreatorDeclaration from './models/CreatorDeclaration';

export default [
  new Extend.Store().add('creator-declarations', CreatorDeclaration),
];
