(() => {
	const onReady = (fn) => {
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', fn);
		} else {
			fn();
		}
	};

	const toIds = (value) =>
		value
			.split(',')
			.map((v) => parseInt(v, 10))
			.filter((v) => Number.isFinite(v) && v > 0);

	onReady(() => {
		const hasMedia = typeof wp !== 'undefined' && !!wp.media;

		const sitePlanInput = document.getElementById('rpa_project_site_plan_id');
		const galleryInput = document.getElementById('rpa_project_gallery_ids');
		const galleryPreview = document.getElementById('rpa_project_gallery_preview');

		const syncGalleryInputFromDom = () => {
			if (!galleryInput || !galleryPreview) return;
			const ids = Array.from(galleryPreview.querySelectorAll('.rpa-gallery-item'))
				.map((el) => parseInt(el.getAttribute('data-id') || '0', 10))
				.filter((v) => Number.isFinite(v) && v > 0);
			galleryInput.value = ids.join(',');
		};

		const ensureGalleryRemoveButtons = () => {
			if (!galleryPreview) return;
			galleryPreview.querySelectorAll('.rpa-gallery-item').forEach((item) => {
				if (item.querySelector('.rpa-gallery-remove')) return;
				const btn = document.createElement('button');
				btn.type = 'button';
				btn.className = 'rpa-gallery-remove';
				btn.setAttribute('aria-label', 'Remove image');
				btn.innerHTML = '<span class="dashicons dashicons-no-alt"></span>';
				item.prepend(btn);
			});
		};

		const initGallerySortable = () => {
			ensureGalleryRemoveButtons();
			const $ = window.jQuery;
			if (!$ || !galleryPreview || !$.fn || !$.fn.sortable) return;

			const $preview = $(galleryPreview);
			if ($preview.data('rpaSortableInit')) return;
			$preview.data('rpaSortableInit', true);

			$preview.sortable({
				items: '.rpa-gallery-item',
				tolerance: 'pointer',
				update: () => syncGalleryInputFromDom(),
			});
		};

		const updateGalleryPreview = (ids) => {
			if (!galleryPreview) return;
			galleryPreview.innerHTML = '';
			ids.forEach((id) => {
				const attachment = wp.media.attachment(id);
				attachment.fetch().then(() => {
					const url =
						attachment.get('sizes') && attachment.get('sizes').thumbnail
							? attachment.get('sizes').thumbnail.url
							: attachment.get('url');
					const span = document.createElement('span');
					span.className = 'rpa-gallery-item';
					span.dataset.id = String(id);
					const btn = document.createElement('button');
					btn.type = 'button';
					btn.className = 'rpa-gallery-remove';
					btn.setAttribute('aria-label', 'Remove image');
					btn.innerHTML = '<span class="dashicons dashicons-no-alt"></span>';
					const img = document.createElement('img');
					img.src = url;
					img.alt = '';
					span.appendChild(btn);
					span.appendChild(img);
					galleryPreview.appendChild(span);
				});
			});
		};

		const initSelect2 = () => {
			const $ = window.jQuery;
			if (!$ || !$.fn || !$.fn.select2) return;

			const $propertyTypes = $('#rpa_project_property_types');
			if ($propertyTypes.length) {
				$propertyTypes.select2({ width: '100%' });
			}
		};

		const initAddresses = () => {
			const container = document.querySelector('[data-rpa-addresses]');
			const addBtn = document.querySelector('[data-rpa-add-address]');
			if (!container || !addBtn) return;

			const max = parseInt(container.getAttribute('data-max') || '0', 10) || 0;

			const renumber = () => {
				const rows = container.querySelectorAll('[data-rpa-address-row]');
				rows.forEach((row, idx) => {
					const label = row.querySelector('label');
					if (label) label.textContent = `Address ${idx + 1}`;
				});
				addBtn.disabled = max > 0 && rows.length >= max;
			};

			addBtn.addEventListener('click', (e) => {
				e.preventDefault();
				const rows = container.querySelectorAll('[data-rpa-address-row]');
				if (max > 0 && rows.length >= max) return;

				const row = document.createElement('div');
				row.className = 'rpa-address';
				row.setAttribute('data-rpa-address-row', '');
				row.innerHTML = `
					<div class="rpa-address-head">
						<label>Address ${rows.length + 1}</label>
						<button type="button" class="button-link-delete" data-rpa-remove-address>Remove</button>
					</div>
					<textarea class="large-text" rows="2" name="rpa_project_addresses[]"></textarea>
				`;
				container.appendChild(row);
				renumber();
			});

			container.addEventListener('click', (e) => {
				const target = e.target;
				if (!(target instanceof HTMLElement)) return;
				const remove = target.closest('[data-rpa-remove-address]');
				if (!remove) return;
				e.preventDefault();
				const rows = container.querySelectorAll('[data-rpa-address-row]');
				const row = remove.closest('[data-rpa-address-row]');
				if (!row) return;
				if (rows.length <= 1) {
					const textarea = row.querySelector('textarea');
					if (textarea) textarea.value = '';
				} else {
					row.remove();
				}
				renumber();
			});

			renumber();
		};

		document.body.addEventListener('click', (e) => {
			const target = e.target;
			if (!(target instanceof HTMLElement)) return;

			const upload = target.closest('[data-rpa-upload]');
			const clear = target.closest('[data-rpa-clear]');
			const galleryRemove = target.closest('.rpa-gallery-remove');

			if (galleryRemove && galleryPreview && galleryPreview.contains(galleryRemove)) {
				e.preventDefault();
				const item = galleryRemove.closest('.rpa-gallery-item');
				if (item) {
					item.remove();
					syncGalleryInputFromDom();
				}
				return;
			}

			if (upload) {
				e.preventDefault();
				if (!hasMedia) return;
				const type = upload.getAttribute('data-rpa-upload');

				if (type === 'site-plan' && sitePlanInput) {
					const frame = wp.media({
						title: 'Select Site Plan',
						button: { text: 'Use this file' },
						multiple: false,
					});
					frame.on('select', () => {
						const attachment = frame.state().get('selection').first().toJSON();
						sitePlanInput.value = String(attachment.id || '');
					});
					frame.open();
				}

				if (type === 'gallery' && galleryInput) {
					const frame = wp.media({
						title: 'Select Gallery Images',
						button: { text: 'Add to gallery' },
						multiple: true,
						library: { type: 'image' },
					});
					frame.on('select', () => {
						const selection = frame.state().get('selection').toJSON();
						const existing = toIds(galleryInput.value || '');
						const added = selection.map((a) => parseInt(a.id, 10)).filter((v) => v > 0);
						const merged = Array.from(new Set([...existing, ...added]));
						galleryInput.value = merged.join(',');
						updateGalleryPreview(merged);
						initGallerySortable();
					});
					frame.open();
				}
			}

			if (clear) {
				e.preventDefault();
				const type = clear.getAttribute('data-rpa-clear');

				if (type === 'site-plan' && sitePlanInput) {
					sitePlanInput.value = '';
				}

				if (type === 'gallery' && galleryInput) {
					galleryInput.value = '';
					updateGalleryPreview([]);
					initGallerySortable();
				}
			}
		});

		initSelect2();
		initAddresses();
		initGallerySortable();
	});
})();
