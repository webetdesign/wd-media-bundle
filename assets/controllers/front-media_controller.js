import { Controller } from "@hotwired/stimulus";

/*
 * WDMediaType js controller
 */
export default class extends Controller {
    static targets = ['addBtn', 'editBtn', 'downloadBtn', 'deleteBtn', 'cropBtn'];
    static values = {
        category: String,
        havefile: Boolean,
        mediaid: Number,
        allowedit: Boolean,
        allowcrop: Boolean,
        allowdownload: Boolean,
        allowdelete: Boolean,
    };

    connect() {
        if (this.havefileValue && Number.isInteger(this.mediaidValue) && this.mediaidValue > 0) {
            // Only have file
            this.hide(this.addBtnTarget);
            this.alloweditValue ? this.show(this.editBtnTarget) : this.hide(this.editBtnTarget);
            this.allowcropValue ? this.show(this.cropBtnTarget) : this.hide(this.cropBtnTarget);
            this.allowdownloadValue ? this.show(this.downloadBtnTarget) : this.hide(this.downloadBtnTarget);
            this.allowdeleteValue ? this.show(this.deleteBtnTarget) : this.hide(this.deleteBtnTarget);
        }else{
            // No file
            this.show(this.addBtnTarget);
            this.hide(this.editBtnTarget);
            this.hide(this.deleteBtnTarget);
            this.hide(this.downloadBtnTarget);
            this.hide(this.cropBtnTarget);
        }
    }

    show(el) {
        el.style.display = 'block';
    }

    hide(el) {
        el.style.display = 'none'
    }
}
