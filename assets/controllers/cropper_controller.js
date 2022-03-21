import { Controller } from 'stimulus';
import 'cropperjs/dist/cropper.css';
import Cropper from 'cropperjs';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
  async connect() {
    this.media = JSON.parse(this.context.element.dataset.media);
    this.config = JSON.parse(this.context.element.dataset.config);
    this.category = this.context.element.dataset.category;
    this.format = this.context.element.dataset.format;
    this.img = this.context.element.querySelector('img');
    this.device = Object.keys(this.config.categories[this.category].formats[this.format])[0];

    this.cropConfigs = this.config.categories[this.category].formats[this.format];
    const cropConfig = this.cropConfigs[this.device].crop;

    this.createCropper(cropConfig);

    this.btnDevices = this.context.element.querySelectorAll('.js-btn-device');
    this.btnDevices.forEach(btn => {
      btn.addEventListener('click', e => this.changeDevice(e));
    });

    this.input = this.context.element.querySelector('input[data-format]');

    let crop = {};
    if (this.media.cropData && this.media.cropData.hasOwnProperty(this.format)) {
      crop = this.media.cropData[this.format];
    }

    this.input.value = JSON.stringify(crop);
  }

  changeDevice(e) {
    const btn = e.currentTarget;
    this.changeColor(btn);

    this.device = btn.dataset.device;

    this.cropper.destroy();
    this.createCropper(this.cropConfigs[this.device].crop);
  }

  changeColor(btn) {
    this.btnDevices.forEach(el => {
      if (el.classList.contains('btn-info')) {
        el.classList.remove('btn-info');
        el.classList.add('btn-default');
      }
    });

    btn.classList.remove('btn-default');
    btn.classList.add('btn-info');
  }

  createCropper(cropConfig) {
    const vm = this;
    let ready = false;
    this.cropper = new Cropper(this.img, {
      width: 1350,
      minContainerWidth: 1300,
      minContainerHeight: 700,
      // aspectRatio: 16 / 9,
      dragMode: 'move',
      cropBoxResizable: false,
      // minCropBoxWidth: 200,
      // minCropBoxHeight: 200,
      // maxCropBoxWidth: 400,
      // maxCropBoxHeight: 400,
      viewMode: 1,
      crop(event) {
        if (ready) {
          const crop = JSON.parse(vm.input.value);
          crop[vm.device] = event.detail;
          vm.input.value = JSON.stringify(crop);
        }
      },
      ready(e) {
        const w = vm.cropper.getImageData().width;
        const h = vm.cropper.getImageData().height;
        const nw = vm.cropper.getImageData().naturalWidth;
        const nh = vm.cropper.getImageData().naturalHeight;


        this.cropper.setCropBoxData({
          width: cropConfig.width * w / nw,
          height: cropConfig.height * h / nh,
        });

        const crop = JSON.parse(vm.input.value);
        if (crop.hasOwnProperty(vm.device)) {
          this.cropper.setData(crop[vm.device]);
        }

        ready = true;
      }
    });
  }
}
