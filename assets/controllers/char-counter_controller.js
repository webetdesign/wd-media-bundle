import { Controller } from 'stimulus';

export default class extends Controller {
  initialize () {
    this.update = this.update.bind(this)
  }

  connect () {
    this.max = this.element.dataset.max !== undefined && this.element.dataset.max !== null ? this.element.dataset.max : 255;
    this.span = document.createElement('span');
    this.element.insertAdjacentElement('afterEnd', this.span);

    this.update()
    this.element.addEventListener('input', this.update)
  }

  disconnect () {
    this.element.removeEventListener('input', this.update)
  }

  update () {
    this.span.innerHTML = this.count
  }

  get count () {
    return `${this.element.value.length} / ${this.max}`
  }
}
