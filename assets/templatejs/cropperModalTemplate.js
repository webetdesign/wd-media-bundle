function tabLinkTpl(prefix_id, code, config, first) {
  const id = prefix_id + '_tab_' + code;
  let active = first ? 'active' : '';
  return `<li class="${active}">
<a href="#${id}" class="changer-tab" aria-controls="${id}" data-toggle="tab">${code}</a>
</li>`;
}

function tabContentTpl(prefix_id, format, category, media, config, first) {
  const id = prefix_id + '_tab_' + format;
  let active = first ? 'active in' : '';

  const configString = JSON.stringify(config);

  let firstA = true;

  return `
<div id="${id}" class="tab-pane fade ${active}">
    <div data-controller="cropper_controller" data-category="${category}" data-media='${JSON.stringify(media)}' data-format="${format}" data-config='${configString}'>
        <input type="hidden" data-format="${format}">
        <div class="img-container">
            <img src="${media.reference}" alt="${media.label}">
        </div>
        <div class="config-container">
            <div class="btn-group">
                ${Object.keys(config.categories[category].formats[format])
    .map((device, key) => {
      if (isDeviceCroppable(config.categories[category].formats[format][device])) {
        const tpl = responsiveBtnTpl(format, device, firstA);
        firstA = false;
        return tpl;
      }
    })
    .join('')}
            </div>
        </div>
    </div>
</div>`;
}

function responsiveBtnTpl(format, device, first) {
  let btnStyle = first ? 'btn-info' : 'btn-default';

  return `<button type="button" class="btn js-btn-device ${btnStyle}" data-device="${device}" data-format="${format}" >${device}</button>`;
}

export function CropperModalBodyTpl(prefix_id, media, category, config) {
  let firstA = true;
  let firstB = true;

  return `
<div>
    <div class="nav-tabs-custom">
        <ul class="nav nav-tabs">
            ${Object.keys(config.categories[category].formats)
    .map((code, key) => {
      if (isCroppable(config.categories[category].formats[code])) {
        const tpl = tabLinkTpl(prefix_id, code, config.categories[category].formats[code], firstA);
        firstA = false;
        return tpl;
      }
    })
    .join('')}
        </ul>
        <div class="tab-content">
            ${Object.keys(config.categories[category].formats)
    .map((code, key) => {
      if (isCroppable(config.categories[category].formats[code])) {
        const tpl = tabContentTpl(prefix_id, code, category, media, config, firstB);
        firstB = false;
        return tpl;
      }
    })
    .join('')}
        </div>
    </div>
</div>`;
}

export function CropperModalFooterTpl() {
  return `<button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
  <button type="button" class="btn btn-primary js-btn-save">Save changes</button>`;
}

export function isCroppable(config) {
  let ret = false;

  Object.keys(config)
    .forEach(device => {
      const conf = config[device];

      if (isDeviceCroppable(conf)) {
        ret = true;
      }
    });

  return ret;
}

function isDeviceCroppable(config) {
  return config.hasOwnProperty('crop') && ((config.crop.width !== null && config.crop.height !== null));
}
