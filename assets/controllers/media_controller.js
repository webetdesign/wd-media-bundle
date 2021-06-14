import { Controller } from 'stimulus';
import axios from 'axios';
import BS3Modal from '../js/BS3Modal';
import { CropperModalBodyTpl, CropperModalFooterTpl, isCroppable } from '../templatejs/cropperModalTemplate';

/*
 * WDMediaType js controller
 */
export default class extends Controller {
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
    };

    this.btn.add.addEventListener('click', e => this.add(e));
    this.btn.edit.addEventListener('click', e => this.edit(e));
    this.btn.list.addEventListener('click', e => this.list(e));
    this.btn.delete.addEventListener('click', e => this.delete(e));
    this.btn.crop.addEventListener('click', e => this.crop(e));

    if (this.isCropable()) {
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
    this.modal = new BS3Modal(this.id + '_modal', `<h3>Edition of ${this.media.label}</h3>` );

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

    axios.get('/admin/webetdesign/media/media/list?' + filters, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(response => {
        this.modal.show();
        this.modal.setBody(response.data);
        this.modal.modal.querySelectorAll('td[objectId]')
          .forEach((td) => {
            td.querySelectorAll('a')
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
      });
  }

  delete(e) {
    this.updateMedia(null);
  }


  submitModalForm(e) {
    e.preventDefault();

    const form = e.target;
    const data = new FormData(form);

    axios.post(form.action, data, {
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then(response => {
        console.log(response);

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
    } else {
      const img = document.createElement('img');
      img.src = media.path;
      img.alt = media.label;
      this.mediaElement.innerHTML = '';
      this.mediaElement.appendChild(img);

      this.input.value = media.id;

      this.btn.edit.classList.remove('d-none');
      this.btn.delete.classList.remove('d-none');
      if (this.isCropable()) {
        this.btn.crop.classList.remove('d-none');
      } else {
        this.btn.crop.classList.add('d-none');
      }
    }
  }

  displayFormError(form, error) {
    const el = form.querySelector('.box-body');

    error.response.data.errors.forEach(err => {
      const errorLine = document.createElement('p');
      errorLine.innerHTML = err;

      el.appendChild(errorLine);
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

    console.log(this.modal.modal.querySelector('.js-btn-save'));

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

    return true;
  }

  isCropable() {
    let ret = false;

    console.log(this.category);

    if (this.media && ['image/jpeg', 'image/png'].includes(this.media.mimeType) ) {
      Object.keys(this.config.categories[this.category].formats)
        .forEach(format => {
          const conf = this.config.categories[this.category].formats[format];

          if (isCroppable(conf)) {
            ret = true;
          }
        });
    }

    console.log(ret);

    return ret;
  }
}
