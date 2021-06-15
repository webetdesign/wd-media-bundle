// dans notre cas, on à un template html prédéfini, du coup je commence par déclarer sa structure par default dans une variable
const innerLink = '<span class="img"><img src="/build/images/intranet/folder-open.svg" alt=""></span><span class="menu__card-content" >Lorem ipsum</span>';
const initBtnTemplate = `<a  href="#" class="menu__card" target="_self" >${innerLink}</a>`;

// on peut redefinir le comportement de CKeditor spécifiquement pour l'intérieur d'un plugin
// par exemple, ne pas supprimer les span vide, si on l'on veut par exemple utiliser <i class="fontawesome-icon"></i>
CKEDITOR.dtd.$removeEmpty.span = false; // inutile dans notre cas, puisqu'on à pas de span vide


//add ( 'nom du plugin', {définition_du_plugin})
CKEDITOR.plugins.add('menuButtonW', {
  requires: 'widget,dialog', // liste des plugins aubligatoires pour faire fonctionner celui-ci
  // fonction d'initialisation du plugin
  init(editor) {
    // on ajouter la dialog/modal de configuration du plugin ( voir plus bas la configuration)
    CKEDITOR.dialog.add('buttonDialog', `${this.path}dialogs/button.js`);
    // ajout d'un bouton dans la toolbar
    editor.ui.addButton('menuButtonW', {
      label: 'menu item',
      command: 'menuButtonWidget', // commande executé au clic, ici l'appel du widget
      icon: `${this.path}icons/simplebutton.png`,
    });
    editor.widgets.add('menuButtonWidget', {
      editables: {
        content: '',
      },
      dialog: 'buttonDialog',
      template: `<div class="menu__item">${initBtnTemplate}</div>`,
      upcast(element) {
        // function pour determiner le hilight/bloc_html qui active le plugin
        return element.name == 'div' && element.hasClass('menu__item');
      },
      // function d'initialistion
      init() {
        const imgUrl = this.element.findOne('img').getAttribute('src');
        const text = this.element.findOne('.menu__card-content').getText();
        const link = this.element.findOne('.menu__card').getAttribute('href');
        const target = this.element.findOne('.menu__card').getAttribute('target');
        // on lie les element du DOM avec le widget
        this.setData('imgUrl', imgUrl);
        this.setData('text', text);
        this.setData('link', link);
        this.setData('target', target);
      },
      // function de traintement des données du widget
      data() {
        if (this.data.imgUrl) {
          this.element.findOne('img').setAttribute('src', this.data.imgUrl);
          this.element.findOne('img').setAttribute('data-cke-saved-src', this.data.imgUrl);
        }
        if (this.data.text) {
          this.element.findOne('.menu__card-content').setText(this.data.text);
        }
        if (this.data.link) {
          this.element.findOne('.menu__card').setAttribute('href', this.data.link);
          this.element.findOne('.menu__card').setAttribute('data-cke-saved-href', this.data.link);
        }
        if (this.data.target) {
          this.element.findOne('.menu__card').setAttribute('target', this.data.target);
        }
      },
    });
  },
});
