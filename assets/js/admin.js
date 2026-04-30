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
		initDocumentManager();
	});

	const initDocumentManager = () => {
		const docInput = document.getElementById('rpa_project_documents_data');
		const docGrid = document.getElementById('rpa-doc-grid');
		const docBreadcrumbs = document.getElementById('rpa-doc-breadcrumbs');
		const btnNewFolder = document.getElementById('rpa-doc-new-folder');
		const btnAddFiles = document.getElementById('rpa-doc-add-files');

		if (!docInput || !docGrid || !docBreadcrumbs || !btnNewFolder || !btnAddFiles) return;

		let docData = [];
		try {
			docData = JSON.parse(docInput.value || '[]');
		} catch (e) {
			docData = [];
		}

		let currentPath = []; // Array of folder objects: { id, name }

		const generateId = () => Math.random().toString(36).substr(2, 9);

		const getCurrentFolderItems = () => {
			let items = docData;
			for (const pathItem of currentPath) {
				const folder = items.find((item) => item.id === pathItem.id);
				if (folder && folder.children) {
					items = folder.children;
				} else {
					return [];
				}
			}
			return items;
		};

		const saveDocData = () => {
			docInput.value = JSON.stringify(docData);
			renderDocManager();
		};

		const getFileIcon = (url) => {
			if (url.match(/\.(jpeg|jpg|gif|png|webp)$/i)) {
				return `<img src="${url}" alt="" class="rpa-doc-item-thumbnail">`;
			}
			if (url.match(/\.pdf$/i)) {
				return '<span class="dashicons dashicons-pdf" style="color: #d63638;"></span>';
			}
			if (url.match(/\.(doc|docx)$/i)) {
				return '<span class="dashicons dashicons-media-document" style="color: #2271b1;"></span>';
			}
			return '<span class="dashicons dashicons-media-default"></span>';
		};

		const renderDocManager = () => {
			// Render Breadcrumbs
			docBreadcrumbs.innerHTML = '';
			const homeLink = document.createElement('span');
			homeLink.className = 'rpa-doc-breadcrumb-item';
			homeLink.textContent = 'Home';
			homeLink.addEventListener('click', () => {
				currentPath = [];
				renderDocManager();
			});
			docBreadcrumbs.appendChild(homeLink);

			currentPath.forEach((pathItem, index) => {
				const separator = document.createElement('span');
				separator.className = 'rpa-doc-breadcrumb-separator';
				separator.textContent = ' / ';
				docBreadcrumbs.appendChild(separator);

				const link = document.createElement('span');
				link.className = 'rpa-doc-breadcrumb-item';
				link.textContent = pathItem.name;
				link.addEventListener('click', () => {
					currentPath = currentPath.slice(0, index + 1);
					renderDocManager();
				});
				docBreadcrumbs.appendChild(link);
			});

			// Render Grid
			docGrid.innerHTML = '';
			const items = getCurrentFolderItems();

			if (items.length === 0) {
				docGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; color: #646970; padding: 20px;">No items found. Create a folder or add files.</div>';
				return;
			}

			items.forEach((item, index) => {
				const itemEl = document.createElement('div');
				itemEl.className = `rpa-doc-item type-${item.type}`;
				
				const iconContainer = document.createElement('div');
				iconContainer.className = 'rpa-doc-item-icon';

				if (item.type === 'folder') {
					iconContainer.innerHTML = '<span class="dashicons dashicons-category"></span>';
					itemEl.addEventListener('click', (e) => {
						if (e.target.closest('.rpa-doc-item-delete')) return;
						currentPath.push({ id: item.id, name: item.name });
						renderDocManager();
					});
				} else {
					iconContainer.innerHTML = getFileIcon(item.url);
					itemEl.addEventListener('click', (e) => {
						if (e.target.closest('.rpa-doc-item-delete')) return;
						window.open(item.url, '_blank');
					});
				}

				const nameContainer = document.createElement('div');
				nameContainer.className = 'rpa-doc-item-name';
				nameContainer.textContent = item.name;
				nameContainer.title = item.name;

				const deleteBtn = document.createElement('div');
				deleteBtn.className = 'rpa-doc-item-delete';
				deleteBtn.innerHTML = '<span class="dashicons dashicons-no-alt" style="font-size: 16px; width: 16px; height: 16px;"></span>';
				deleteBtn.addEventListener('click', (e) => {
					e.stopPropagation();
					if (confirm('Are you sure you want to delete this item?')) {
						items.splice(index, 1);
						saveDocData();
					}
				});

				itemEl.appendChild(iconContainer);
				itemEl.appendChild(nameContainer);
				itemEl.appendChild(deleteBtn);
				docGrid.appendChild(itemEl);
			});
		};

		btnNewFolder.addEventListener('click', () => {
			const folderName = prompt('Enter folder name:');
			if (folderName && folderName.trim() !== '') {
				const items = getCurrentFolderItems();
				items.push({
					id: generateId(),
					type: 'folder',
					name: folderName.trim(),
					children: []
				});
				saveDocData();
			}
		});

		btnAddFiles.addEventListener('click', () => {
			const hasMedia = typeof wp !== 'undefined' && !!wp.media;
			if (!hasMedia) return;
			const frame = wp.media({
				title: 'Select Files',
				button: { text: 'Add to current folder' },
				multiple: true
			});

			frame.on('select', () => {
				const selection = frame.state().get('selection').toJSON();
				const items = getCurrentFolderItems();
				
				selection.forEach((attachment) => {
					items.push({
						id: generateId(),
						type: 'file',
						name: attachment.filename || attachment.title,
						url: attachment.url,
						attachment_id: attachment.id
					});
				});
				saveDocData();
			});

			frame.open();
		});

		renderDocManager();
	};

	// Handle Send Email button in Deal Entry meta box
	onReady(() => {
		const resendBtn = document.getElementById('rpa-resend-magic-link');
		if (resendBtn) {
			// Add hover effect via JS since inline styles don't support pseudo-classes
			resendBtn.addEventListener('mouseenter', function() {
				if (!this.disabled) this.style.backgroundColor = '#2563eb';
			});
			resendBtn.addEventListener('mouseleave', function() {
				if (!this.disabled) this.style.backgroundColor = '#3b82f6';
			});

			resendBtn.addEventListener('click', function (e) {
				e.preventDefault();
				const entryId = this.getAttribute('data-entry-id');
				const msgEl = document.getElementById('rpa-resend-msg');

				this.disabled = true;
				this.style.opacity = '0.7';
				this.style.cursor = 'not-allowed';
				this.innerHTML = '<svg class="rpa-spinner" viewBox="0 0 50 50" style="width: 14px; height: 14px; animation: rpa-spin 1s linear infinite;"><circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" stroke-dasharray="31.4 31.4" style="stroke-linecap: round;"></circle></svg> Sending...';
				msgEl.textContent = '';
				msgEl.style.color = '';

				jQuery.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'rpa_resend_magic_link',
						security: window.rpaListingsAdmin ? window.rpaListingsAdmin.nonce : '',
						entry_id: entryId
					},
					success: function (res) {
						resendBtn.disabled = false;
						resendBtn.style.opacity = '1';
						resendBtn.style.cursor = 'pointer';
						resendBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Send Email';
						
						if (res.success) {
							msgEl.style.color = '#16a34a';
							msgEl.textContent = res.data.message;
							setTimeout(() => { msgEl.textContent = ''; }, 3000);
						} else {
							msgEl.style.color = '#dc2626';
							msgEl.textContent = res.data.message || 'Error sending email.';
						}
					},
					error: function () {
						resendBtn.disabled = false;
						resendBtn.style.opacity = '1';
						resendBtn.style.cursor = 'pointer';
						resendBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg> Send Email';
						msgEl.style.color = '#dc2626';
						msgEl.textContent = 'Server error.';
					}
				});
			});
		}
	});
})();
