import { Controller } from 'stimulus';
import axios from 'axios';
import BS3Modal from '../js/BS3Modal';
import {
  CropperModalBodyTpl,
  CropperModalFooterTpl,
  isCroppable
} from '../templatejs/cropperModalTemplate';
import toastr from 'toastr';
import '../../node_modules/toastr/build/toastr.min.css';
/*
 * WDMediaType js controller
 */
export default class extends Controller {
  static targets = ['btnGroup', 'btnAdd', 'btnEdit', 'btnList', 'btnDelete', 'btnCrop'];

  async connect() {
    this.config = {
      responsive: JSON.parse(this.context.element.dataset.configResponsive),
      categories: JSON.parse(this.context.element.dataset.configCategories),
    };

    this.id = this.context.element.dataset.id;
    this.category = this.context.element.dataset.category;
    this.mediaId = this.context.element.dataset.mediaId;

    this.media = this.mediaId ? await this.getMedia(this.mediaId) : null;

    this.input = this.context.element.querySelector('input[type="hidden"]');

    this.mediaElement = this.context.element.querySelector('.image');

    this.btn = {
      add: this.context.element.querySelector('.js-btn-add'),
      edit: this.context.element.querySelector('.js-btn-edit'),
      list: this.context.element.querySelector('.js-btn-list'),
      delete: this.context.element.querySelector('.js-btn-delete'),
      crop: this.context.element.querySelector('.js-btn-crop'),
      links: this.context.element.querySelectorAll('.js-btn-link'),
    };

    this.btn.add ? this.btn.add.addEventListener('click', e => this.add(e)) : null;
    this.btn.edit ? this.btn.edit.addEventListener('click', e => this.edit(e)) : null;
    this.btn.list ? this.btn.list.addEventListener('click', e => this.list(e)) : null;
    this.btn.delete ? this.btn.delete.addEventListener('click', e => this.delete(e)) : null;
    this.btn.crop ? this.btn.crop.addEventListener('click', e => this.crop(e)) : null;
    this.btn.links ? this.btn.links.forEach((link) => link.addEventListener('click', e => this.link(e))) : null;

    if (this.isCropable() && this.btn.crop) {
      this.btn.crop.classList.remove('d-none');
    }
  }

  add(e) {
    this.modal = new BS3Modal(this.id + '_modal', `<h3> New media in ${this.config.categories[this.category].label} category </h3>`);

    const queryString = new URLSearchParams();
    queryString.set('category', this.category);

    axios.get('/admin/webetdesign/media/media/create?' + queryString.toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(response => {
        this.modal.show();
        this.modal.setBody(response.data);
        this.modal.modal.querySelector('form')
          .addEventListener('submit', e => {
            this.submitModalForm(e);
          });
      });
  }

  edit(e) {
    this.modal = new BS3Modal(this.id + '_modal', `<h3>Edition of ${this.media.label}</h3>`);

    axios.get('/admin/webetdesign/media/media/' + this.mediaId + '/edit', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(response => {
        this.modal.show();
        this.modal.setBody(response.data);
        this.modal.modal.querySelector('form')
          .addEventListener('submit', e => {
            this.submitModalForm(e);
          });
      });
  }

  list(e) {
    this.modal = new BS3Modal(this.id + '_modal', `<h3>List of media in the ${this.config.categories[this.category].label} category</h3>`);

    const filters = 'filter%5Bcategory%5D%5Btype%5D=3&filter%5Bcategory%5D%5Bvalue%5D=' + this.category;

    const uri = '/admin/webetdesign/media/media/list?' + filters;

    this.fetchList(uri);
  }

  fetchList(uri) {
    axios.get(uri, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(response => {
        this.modal.show();
        this.modal.setBody(response.data);
        this.modal.modal.querySelectorAll('td[objectId]')
          .forEach((td) => {
            td.querySelectorAll('a:not(.disable-catch)')
              .forEach(a => {
                a.addEventListener('click', async evt => {
                  evt.preventDefault();
                  const media = await this.getMedia(td.getAttribute('objectId'));
                  this.updateMedia(media);
                  this.modal.hide();
                  this.modal.destroy();
                });
              });
          });

        this.modal.modal.querySelectorAll('.pagination a')
          .forEach(link => {
            link.addEventListener('click', e => {
              e.preventDefault();

              this.fetchList(link.href);
            });
          });
      });
  }

  delete(e) {
    this.updateMedia(null);
  }


  submitModalForm(e) {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    form.querySelectorAll('.has-error')
      .forEach(el => {
        el.classList.remove('has-error');
      });

    form.querySelectorAll('.sonata-ba-field-error-messages')
      .forEach(el => {
        el.remove();
      });

    axios.post(form.action, data, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(response => {
        this.updateMedia(response.data.media);

        this.modal.hide();
        this.modal.destroy();

      })
      .catch(error => {
        this.displayFormError(form, error);
      });
  }

  updateMedia(media) {
    this.media = media;
    if (media === null) {
      this.mediaElement.innerHTML = 'Aucun media';

      this.input.value = null;
      this.btn.edit.classList.add('d-none');
      this.btn.delete.classList.add('d-none');
      this.btn.crop.classList.add('d-none');
      this.btnGroupTarget.classList.add('btn-group')
      this.btnGroupTarget.classList.remove('btn-group-vertical')
    } else {
      const img = document.createElement('img');
      img.src = media.path;
      img.alt = media.label;
      if (media.mimeType === 'image/svg+xml') {
        console.log(media);
        img.style.width = '150px';
      }
      this.mediaElement.innerHTML = '';
      this.mediaElement.appendChild(img);
      this.input.value = media.id;
      this.mediaId = media.id;
      this.btn.edit.classList.remove('d-none');
      this.btn.delete.classList.remove('d-none');
      this.btnGroupTarget.classList.remove('btn-group')
      this.btnGroupTarget.classList.add('btn-group-vertical')
      if (this.isCropable()) {
        this.btn.crop.classList.remove('d-none');
      } else {
        this.btn.crop.classList.add('d-none');
      }
    }
  }

  displayFormError(form, error) {
    Object.entries(error.response.data.errors)
      .forEach((err) => {
        let input = form.querySelector(`input[id$="${err[0]}"]`);
        if (input) {
          let container = input.parentNode.parentNode;
          container.classList.add('has-error');

          let errorContainer = document.createElement('div');
          errorContainer.classList.add('help-block', 'sonata-ba-field-error-messages');

          let errorUl = document.createElement('ul');
          errorUl.classList.add('list-unstyled');

          let errorLi = document.createElement('li');
          errorLi.innerHTML = err[1];

          console.log(errorLi, errorUl, errorContainer);

          errorUl.appendChild(errorLi);
          errorContainer.appendChild(errorUl);
          input.after(errorContainer);
        }

      });
  }

  async getMedia(id) {
    if (id === null) {
      throw new Error('id can not be null');
    }

    const response = await axios.get('/api/wdmedia/' + id);
    return response.data;
  }

  crop(e) {

    const body = CropperModalBodyTpl(this.id, this.media, this.category, this.config);

    const footer = CropperModalFooterTpl();

    this.modal = new BS3Modal(this.id + '_modal', 'Crop ');
    this.modal.setBody(body);
    this.modal.setFooter(footer);

    this.modal.modal.querySelector('.js-btn-save')
      .addEventListener('click', async e => {
        const ret = await this.saveCrop();

        if (ret) {
          this.modal.hide();
          this.modal.destroy();
        }

      });

    this.modal.show();
  }

  async saveCrop() {
    let crop = {};

    this.modal.modal.querySelectorAll('input[data-format]')
      .forEach(input => {
        crop[input.dataset.format] = JSON.parse(input.value);
      });

    const data = new FormData();
    data.append('cropData', JSON.stringify(crop));

    try {
      const response = await axios.post('/api/wdmedia/setcrop/' + this.media.id, data);
    } catch (error) {
      return false;
    }

    this.media.cropData = crop;

    return true;
  }

  isCropable() {
    let ret = false;

    if (this.media && ['image/jpeg', 'image/png'].includes(this.media.mimeType)) {
      Object.keys(this.config.categories[this.category].formats)
        .forEach(format => {
          const conf = this.config.categories[this.category].formats[format];

          if (isCroppable(conf)) {
            ret = true;
          }
        });
    }

    return ret;
  }

  link(e) {
    let btn = e.target;
    let { link } = btn.dataset;
    navigator.clipboard.writeText(link);
    toastr.success(link, 'Lien copié dans le presse-papier');
  }
}
