import app from 'flarum/forum/app';
import Component from 'flarum/common/Component';
import icon from 'flarum/common/helpers/icon';
import CreatorDeclaration from '../../common/models/CreatorDeclaration';
import { definitionFor, isSourceDeclarationKey } from '../utils/declarations';
import type Mithril from 'mithril';

interface SourceDeclarationAttrs {
  declarations: CreatorDeclaration[];
}

const sourceIcons: Record<string, string> = {
  original: 'fas fa-pen-nib',
  repost: 'fas fa-link',
  reference: 'fas fa-quote-left',
};
export default class SourceDeclaration extends Component<SourceDeclarationAttrs> {
  view(vnode: Mithril.Vnode<SourceDeclarationAttrs, this>) {
    const hasOtherDeclarations = vnode.attrs.declarations.some(
      (item) =>
        definitionFor(item.key()) && !isSourceDeclarationKey(item.key()),
    );

    if (hasOtherDeclarations) return null;

    const declaration = vnode.attrs.declarations.find((item) =>
      isSourceDeclarationKey(item.key()),
    );

    if (!declaration) return null;

    if (declaration.key() === 'original') {
      return (
        <section className="CreatorDeclarationSource">
          <span>
            {app.translator.trans(
              'ffans-creator-declarations.forum.display.original_notice',
            )}
          </span>
        </section>
      );
    }

    const source = declaration.metadata()?.details;
    const title = declaration.metadata()?.title;

    if (!source) return null;

    return (
      <section className="CreatorDeclarationSource">
        {icon(sourceIcons[declaration.key()], {
          className: `CreatorDeclarationSource-icon CreatorDeclarationSource-icon--${declaration.key()}`,
        })}
        <span>
          {app.translator.trans(
            `ffans-creator-declarations.forum.display.${declaration.key()}_notice`,
            {
              source: (
                <a
                  href={source}
                  target="_blank"
                  rel="noopener noreferrer nofollow ugc"
                >
                  {title || source}
                </a>
              ),
            },
          )}
        </span>
      </section>
    );
  }
}
