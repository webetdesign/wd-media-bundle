
const $ = require('jquery');
require('bootstrap');

const modalTpl = `<div
            class="modal fade"
            tabindex="-1"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-lg" style="width: 90%; height: 85%; padding: 0;">
                <div class="modal-content" style="border-radius: 0; height: 100%; padding: 0;">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                    </div>
                    <div class="modal-body" style="overflow: scroll; height: 85%">
                    </div>
                    <div class="modal-footer">
                    </div>
                </div>
            </div>
        </div>`


/*
 * Bootstrap 3 modal helper
 */
export default class {
  modal;
  constructor(id, title = null, body = null, footer = null) {

    this.modal = this.createDomElement(modalTpl);
    this.modal.id = id;

    document.body.appendChild(this.modal);

    this.setTitle(title);
    this.setBody(body);
    this.setFooter(footer);
  }

  createDomElement(html) {
    const template = document.createElement('template')
    template.innerHTML = html;
    return template.content.firstElementChild;
  }

  show() {
    $(this.modal).modal('show');
  }

  hide() {
    $(this.modal).modal('hide');
  }

  toggle() {
    $(this.modal).modal('toggle');
  }

  setBody(body) {
    this.modal.querySelector('.modal-body').innerHTML = body;
  }

  setTitle(title) {
    this.modal.querySelector('.modal-title').innerHTML = title;
  }

  setFooter(footer) {
    this.modal.querySelector('.modal-footer').innerHTML = footer;
  }

  destroy() {
    this.modal.remove();
  }

}
