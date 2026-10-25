(() => {
  'use strict';
  const form = document.querySelector('[data-admin-editor-form]');
  if (!form) return;
  const error = form.querySelector('[data-editor-error]');
  const showError = message => { error.textContent = message; error.hidden = false; };
  const token = document.querySelector('meta[name="csrf-token"]').content;
  const initial = new Map([...form.querySelectorAll('textarea.admin-rich-editor')].map(el => [el.id, el.value]));
  const upload = (file, progress = () => {}) => new Promise((resolve, reject) => {
    if (!['image/jpeg','image/png','image/gif','image/webp'].includes(file.type)) return reject(new Error('Choose a JPG, PNG, GIF or WebP image.'));
    if (file.size > 10 * 1024 * 1024) return reject(new Error('Images must be 10 MB or smaller.'));
    const xhr = new XMLHttpRequest();
    xhr.open('POST', form.dataset.imageUpload);
    xhr.setRequestHeader('X-CSRF-TOKEN', token);
    xhr.setRequestHeader('Accept', 'application/json');
    xhr.upload.onprogress = event => { if (event.lengthComputable) progress(event.loaded / event.total * 100); };
    xhr.onerror = () => reject(new Error('The image could not be uploaded. Check your connection and retry.'));
    xhr.onload = () => {
      let data;
      try { data = JSON.parse(xhr.responseText); } catch { return reject(new Error('The upload server returned an unexpected response. Please sign in again if your session expired.')); }
      if (xhr.status < 200 || xhr.status >= 300) return reject(new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Image upload failed.'));
      if (!data.location) return reject(new Error('The server did not return an image URL.'));
      resolve(data.location);
    };
    const body = new FormData();
    body.append('file', file, file.name || 'image.png');
    body.append('resource', form.dataset.resource);
    xhr.send(body);
  });
  if (initial.size) {
    if (!window.tinymce) showError('The editor could not load. You can edit the HTML in the text fields below.');
    else tinymce.init({
      selector: 'textarea.admin-rich-editor',
      base_url: '/vendor/tinymce', suffix: '.min', license_key: 'gpl',
      plugins: 'advlist anchor autolink charmap code fullscreen image link lists media preview help searchreplace table visualblocks wordcount',
      menubar: 'edit view insert format tools table help',
      toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | link image table | alignleft aligncenter alignright | outdent indent | removeformat | code preview fullscreen',
      toolbar_mode: 'sliding', height: 420, min_height: 280, resize: true,
      promotion: false, branding: true,
      add_form_submit_trigger: false, add_unload_trigger: false, submit_patch: false,
      content_css: '/frontend/css/admin-editor-content.css?v=20261024-1',
      block_formats: 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4',
      image_caption: true, image_advtab: true, image_title: true,
      automatic_uploads: true, paste_data_images: true,
      images_file_types: 'jpg,jpeg,png,gif,webp',
      images_upload_handler: (blobInfo, progress) => upload(new File([blobInfo.blob()], blobInfo.filename(), {type: blobInfo.blob().type}), progress),
      file_picker_types: 'image',
      file_picker_callback: (callback) => {
        const picker = document.createElement('input');
        picker.type = 'file'; picker.accept = 'image/jpeg,image/png,image/gif,image/webp';
        picker.onchange = async () => {
          const file = picker.files[0]; if (!file) return;
          try { const url = await upload(file); callback(url, {alt: '', title: file.name}); }
          catch (e) { tinymce.activeEditor.windowManager.alert(e.message); }
        };
        picker.click();
      },
      relative_urls: false, remove_script_host: true, convert_urls: true,
      link_default_target: '_blank', link_assume_external_targets: 'https',
      setup: editor => {
        editor.on('init', () => { editor.setDirty(false); });
        editor.on('BeforeSetContent', event => {
          const doc = new DOMParser().parseFromString(event.content, 'text/html');
          // Resolve the legacy site's images through the local media archive.
          doc.querySelectorAll('img[src]').forEach(image => {
            try {
              const url = new URL(image.getAttribute('src'), location.origin);
              if (['eternityinsights.com','www.eternityinsights.com'].includes(url.hostname) && url.pathname.startsWith('/uploads/')) image.setAttribute('src', url.pathname);
            } catch {}
          });
          // Keep existing sample CTAs functional without editable inline JavaScript.
          doc.querySelectorAll('button[onclick]').forEach(button => {
            const match = button.getAttribute('onclick').match(/^\s*window\.location\.href\s*=\s*['"](\/[^'"]*)['"];?\s*$/);
            if (!match || match[1].startsWith('//')) return;
            const link = doc.createElement('a');
            link.className = button.className; link.href = match[1]; link.innerHTML = button.innerHTML;
            button.replaceWith(link);
          });
          event.content = doc.body.innerHTML;
        });
      },
    }).catch(e => showError('The editor could not start: ' + e.message));
  }
  const field = form.querySelector('[data-image-field]');
  if (field) {
    const input = field.querySelector('#featured_img');
    const file = field.querySelector('[data-featured-upload]');
    const preview = field.querySelector('[data-featured-preview]');
    let objectUrl;
    const render = url => { preview.hidden = !url; if (url) preview.src = url; else preview.removeAttribute('src'); };
    input.addEventListener('input', () => render(input.value.trim()));
    file.addEventListener('change', () => {
      if (objectUrl) URL.revokeObjectURL(objectUrl);
      if (file.files[0]) { objectUrl = URL.createObjectURL(file.files[0]); render(objectUrl); }
      else render(input.value.trim());
    });
    preview.addEventListener('error', () => { preview.hidden = true; });
    field.querySelector('[data-clear-image]').addEventListener('click', () => { input.value = ''; file.value = ''; render(''); });
  }
  // Business-friendly repeatable rows use ordinary form fields, with no JSON editing.
  for (const kind of ['segment', 'license']) {
    const container = form.querySelector('[data-report-rows="' + kind + '"]');
    if (!container) continue;
    const add = form.querySelector('[data-add-report-row="' + kind + '"]');
    const empty = form.querySelector('[data-empty-rows="' + kind + '"]');
    let next = Math.max(-1, ...[...container.querySelectorAll('[name]')].map(input => Number(input.name.match(/\[(\d+)\]/)?.[1] ?? -1))) + 1;
    const refresh = () => { empty.hidden = container.querySelectorAll('[data-report-row]').length > 0; };
    add.addEventListener('click', () => {
      const template = document.getElementById('report-' + kind + '-template');
      container.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', String(next++)));
      refresh(); container.lastElementChild.querySelector('input:not([type="hidden"])').focus();
    });
    container.addEventListener('click', event => {
      const remove = event.target.closest('[data-remove-report-row]');
      if (!remove) return;
      const row = remove.closest('[data-report-row]');
      const nextRow = row.nextElementSibling || row.previousElementSibling;
      row.remove(); refresh();
      if (nextRow) nextRow.querySelector('input:not([type="hidden"])').focus(); else add.focus();
    });
    refresh();
  }
  let readyToSubmit = false, submitting = false;
  form.addEventListener('submit', async event => {
    if (readyToSubmit) return;
    event.preventDefault(); if (submitting) return;
    submitting = true; error.hidden = true;
    const button = event.submitter || form.querySelector('[type="submit"]');
    const label = button.textContent; button.disabled = true; button.textContent = 'Saving...';
    try {
      const editors = window.tinymce ? tinymce.get().filter(editor => form.contains(editor.getElement())) : [];
      for (const editor of editors) {
        const uploaded = await editor.uploadImages();
        if (uploaded.some(result => result.status === false)) throw new Error('An image could not be uploaded. Please retry before saving.');
        if (editor.isDirty()) editor.save();
        else editor.getElement().value = initial.get(editor.id);
      }
      // Let the original submit event finish before requesting a second submission.
      await new Promise(resolve => setTimeout(resolve, 0));
      readyToSubmit = true; button.disabled = false; form.requestSubmit(button);
    } catch (e) {
      showError(e.message || 'Unable to save. Please try again.');
      error.scrollIntoView({block: 'center', behavior: 'smooth'});
    } finally { submitting = false; button.disabled = false; button.textContent = label; }
  });
})();
