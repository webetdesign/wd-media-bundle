//https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_dtd.html
CKEDITOR.dtd.$removeEmpty.span = 0; // modification de la dtd supprimant les span vide

const innerTemplate = '<span class="img"><img src="/build/images/intranet/folder-open.svg" alt="" contenteditable="false"></span><span class="menu__card-content" contenteditable="false">Lorem ipsum</span>';
const btnTemplate = `<a  href="#" class="menu__card" target="_self" contenteditable="false">${innerTemplate}</a>`;

CKEDITOR.dialog.add('buttonDialog', editor => ({
  title: 'Button', // titre de la modal affiché
  minWidth: 400,
  minHeight: 300,
  contents: [
    {
      id: 'tab-basic',
      elements: [ // liste des inputs et autres ellement configurable https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_dialog_definition.html pour la liste des type différent
        {
          type: 'hbox',
          widths: ['280px', '110px'],
          align: 'right',
          className: 'cke_dialog_image_url',
          children: [
            {
              type: 'text',
              id: 'imgUrl',
              label: 'image url',
              setup(widget) {
                this.setValue(widget.data.imgUrl);
              },
              commit(widget) {
                widget.setData('imgUrl', this.getValue());
              },
              onChange() {
                document.querySelector('#btn-preview .img img').setAttribute('src', this.getValue());
              },
            },
            {
              type: 'button',
              id: 'browse',
              label: editor.lang.common.browseServer,
              hidden: true,
              filebrowser: 'tab-basic:imgUrl', // permet de remplir automatiquement le champ text ayant l'id imgUrl dans la tab id tab-basic
            },
          ],
        },
        {
          type: 'text',
          id: 'button-text',
          label: 'Text',
          validate: CKEDITOR.dialog.validate.notEmpty('Le champ text ne doit pas être vide.'), // liste des validation possible https://ckeditor.com/docs/ckeditor4/latest/api/CKEDITOR_dialog_validate.html
          setup(widget) {
            // on init la valeur de l'input avec celle du widget
            this.setValue(widget.data.text);
          },
          commit(widget) {
            //a l'enregistrement au set la valeur du widget avec celle de l'input
            widget.setData('text', this.getValue());
          },
          onChange() {
            // on maj la preview au changement de valeur
            document.querySelector('#btn-preview .menu__card-content').innerHTML = this.getValue();
          },
        },
        {
          type: 'text',
          id: 'button-url',
          label: 'URL',
          setup(widget) {
            this.setValue(widget.data.link);
          },
          commit(widget) {
            widget.setData('link', this.getValue());
          },
          onChange() {
            document.querySelector('#btn-preview a').setAttribute('href', this.getValue());
          },
        },
        {
          type: 'select',
          id: 'button-target',
          label: 'Target',
          items: [['_self'], ['_blank']],
          default: '_self',
          setup(widget) {
            this.setValue(widget.data.target);
          },
          commit(widget) {
            widget.setData('target', this.getValue());
          },
          onChange() {
            document.querySelector('#btn-preview a').setAttribute('target', this.getValue());
          },
        },
        {
          type: 'html',
          id: 'preview-html',
          allowedContent: 'span(*)[*]',
          html: `<p>Preview</p><div id="btn-preview">${btnTemplate}</div>`,
        },
      ],
    },
  ],

}));
