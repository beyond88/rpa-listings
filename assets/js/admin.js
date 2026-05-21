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

	// --- Component Handlers ---

	function initGalleryManager() {
		const hasMedia = typeof wp !== 'undefined' && !!wp.media;
		const galleryInput = document.getElementById('rpa_project_gallery_ids');
		const galleryPreview = document.getElementById('rpa_project_gallery_preview');
		if (!galleryInput || !galleryPreview) return;

		const syncGalleryInputFromDom = () => {
			const ids = Array.from(galleryPreview.querySelectorAll('.rpa-gallery-item'))
				.map((el) => parseInt(el.getAttribute('data-id') || '0', 10))
				.filter((v) => Number.isFinite(v) && v > 0);
			galleryInput.value = ids.join(',');
		};

		const ensureGalleryRemoveButtons = () => {
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

		const initSortable = () => {
			ensureGalleryRemoveButtons();
			const $ = window.jQuery;
			if (!$ || !$.fn || !$.fn.sortable) return;
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

		// Initial sortable setup
		initSortable();

		// Event Delegation for Upload/Clear/Remove
		document.body.addEventListener('click', (e) => {
			const target = e.target;
			if (!(target instanceof HTMLElement)) return;

			const upload = target.closest('[data-rpa-upload]');
			const clear = target.closest('[data-rpa-clear]');
			const galleryRemove = target.closest('.rpa-gallery-remove');

			if (galleryRemove && galleryPreview.contains(galleryRemove)) {
				e.preventDefault();
				galleryRemove.closest('.rpa-gallery-item')?.remove();
				syncGalleryInputFromDom();
				return;
			}

			if (upload) {
				const type = upload.getAttribute('data-rpa-upload');
				if (type === 'gallery' && hasMedia) {
					e.preventDefault();
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
						initSortable();
					});
					frame.open();
				}
			}

			if (clear && clear.getAttribute('data-rpa-clear') === 'gallery') {
				e.preventDefault();
				galleryInput.value = '';
				updateGalleryPreview([]);
			}
		});
	}

	function initSingleUploads() {
		const hasMedia = typeof wp !== 'undefined' && !!wp.media;
		const sitePlanInput = document.getElementById('rpa_project_site_plan_id');

		document.body.addEventListener('click', (e) => {
			const target = e.target;
			if (!(target instanceof HTMLElement)) return;

			const upload = target.closest('[data-rpa-upload]');
			const clear = target.closest('[data-rpa-clear]');

			if (upload && hasMedia) {
				const type = upload.getAttribute('data-rpa-upload');
				if (type === 'site-plan' && sitePlanInput) {
					e.preventDefault();
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

				if (type === 'video' || type === 'video-360') {
					e.preventDefault();
					const is360 = type === 'video-360';
					const input = document.getElementById(is360 ? 'rpa_project_360_video_id' : 'rpa_project_video_id');
					const preview = document.getElementById(is360 ? 'rpa_project_360_video_preview' : 'rpa_project_video_preview');

					const frame = wp.media({
						title: is360 ? 'Select 360 Video' : 'Select Video',
						button: { text: 'Use this file' },
						multiple: false,
					});
					frame.on('select', () => {
						const attachment = frame.state().get('selection').first().toJSON();
						if (input) input.value = String(attachment.id || '');
						if (preview) preview.textContent = attachment.filename || attachment.title;
					});
					frame.open();
				}
			}

			if (clear) {
				const type = clear.getAttribute('data-rpa-clear');
				if (type === 'site-plan' && sitePlanInput) {
					e.preventDefault();
					sitePlanInput.value = '';
				}
				if (type === 'video' || type === 'video-360') {
					e.preventDefault();
					const is360 = type === 'video-360';
					const input = document.getElementById(is360 ? 'rpa_project_360_video_id' : 'rpa_project_video_id');
					const preview = document.getElementById(is360 ? 'rpa_project_360_video_preview' : 'rpa_project_video_preview');
					if (input) input.value = '';
					if (preview) preview.textContent = is360 ? 'No 360 video selected' : 'No video selected';
				}
			}
		});
	}

	function initSelect2() {
		const $ = window.jQuery;
		if (!$ || !$.fn || !$.fn.select2) return;
		const $propertyTypes = $('#rpa_project_property_types');
		if ($propertyTypes.length) {
			$propertyTypes.select2({ width: '100%' });
		}
	}

	function initDocumentManager() {
		const docInput = document.getElementById('rpa_project_documents_data');
		const docGrid = document.getElementById('rpa-doc-grid');
		const docBreadcrumbs = document.getElementById('rpa-doc-breadcrumbs');
		const btnNewFolder = document.getElementById('rpa-doc-new-folder');
		const btnAddFiles = document.getElementById('rpa-doc-add-files');

		if (!docInput || !docGrid || !docBreadcrumbs || !btnNewFolder || !btnAddFiles) return;

		let docData = [];
		try { docData = JSON.parse(docInput.value || '[]'); } catch (e) { docData = []; }

		let currentPath = [];
		const generateId = () => Math.random().toString(36).substr(2, 9);

		const getCurrentFolderItems = () => {
			let items = docData;
			for (const pathItem of currentPath) {
				const folder = items.find((item) => item.id === pathItem.id);
				if (folder && folder.children) items = folder.children;
				else return [];
			}
			return items;
		};

		const saveDocData = () => {
			docInput.value = JSON.stringify(docData);
			renderDocManager();
		};

		const getFileIcon = (url) => {
			if (url.match(/\.(jpeg|jpg|gif|png|webp)$/i)) return `<img src="${url}" alt="" class="rpa-doc-item-thumbnail">`;
			if (url.match(/\.pdf$/i)) return '<span class="dashicons dashicons-pdf" style="color: #d63638;"></span>';
			if (url.match(/\.(doc|docx)$/i)) return '<span class="dashicons dashicons-media-document" style="color: #2271b1;"></span>';
			return '<span class="dashicons dashicons-media-default"></span>';
		};

		const renderDocManager = () => {
			docBreadcrumbs.innerHTML = '';
			const homeLink = document.createElement('span');
			homeLink.className = 'rpa-doc-breadcrumb-item';
			homeLink.textContent = 'Home';
			homeLink.addEventListener('click', () => { currentPath = []; renderDocManager(); });
			docBreadcrumbs.appendChild(homeLink);

			currentPath.forEach((pathItem, index) => {
				const separator = document.createElement('span');
				separator.className = 'rpa-doc-breadcrumb-separator';
				separator.textContent = ' / ';
				docBreadcrumbs.appendChild(separator);

				const link = document.createElement('span');
				link.className = 'rpa-doc-breadcrumb-item';
				link.textContent = pathItem.name;
				link.addEventListener('click', () => { currentPath = currentPath.slice(0, index + 1); renderDocManager(); });
				docBreadcrumbs.appendChild(link);
			});

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
				getCurrentFolderItems().push({ id: generateId(), type: 'folder', name: folderName.trim(), children: [] });
				saveDocData();
			}
		});

		btnAddFiles.addEventListener('click', () => {
			if (typeof wp === 'undefined' || !wp.media) return;
			const frame = wp.media({ title: 'Select Files', button: { text: 'Add to current folder' }, multiple: true });
			frame.on('select', () => {
				const selection = frame.state().get('selection').toJSON();
				const items = getCurrentFolderItems();
				selection.forEach((attachment) => {
					items.push({ id: generateId(), type: 'file', name: attachment.filename || attachment.title, url: attachment.url, attachment_id: attachment.id });
				});
				saveDocData();
			});
			frame.open();
		});

		renderDocManager();
	}

	function initVideoGalleryManager() {
		const videoInput = document.getElementById('rpa_project_video_gallery_data');
		const videoList = document.getElementById('rpa-video-list');
		const btnAddRow = document.getElementById('rpa-video-add-row');

		if (!videoInput || !videoList || !btnAddRow) return;

		let videoData = [];
		try { videoData = JSON.parse(videoInput.value || '[]'); } catch (e) { videoData = []; }

		const saveVideoData = () => {
			const rows = Array.from(videoList.querySelectorAll('.rpa-video-item'));
			const videoData = rows.map(row => {
				const urlInput = row.querySelector('.rpa-video-url');
				const thumbIdInput = row.querySelector('.rpa-video-thumb-id');
				const thumbUrlInput = row.querySelector('.rpa-video-thumb-url');
				
				return {
					type: 'external',
					name: 'Video',
					url: urlInput ? urlInput.value.trim() : '',
					thumbnail_id: thumbIdInput ? thumbIdInput.value : '',
					thumbnail_url: thumbUrlInput ? thumbUrlInput.value : ''
				};
			}).filter(v => v.url !== '');
			
			videoInput.value = JSON.stringify(videoData);
			$(videoInput).trigger('change'); // Ensure any other listeners are aware
		};

		const createRow = (item = { url: '', thumbnail_id: '', thumbnail_url: '' }) => {
			const itemEl = document.createElement('div');
			itemEl.className = 'rpa-video-item';
			itemEl.style.display = 'flex';
			itemEl.style.flexDirection = 'column';
			itemEl.style.gap = '10px';
			itemEl.style.marginBottom = '15px';
			itemEl.style.padding = '12px';
			itemEl.style.border = '1px solid #c3c4c7';
			itemEl.style.background = '#fff';
			itemEl.style.borderRadius = '4px';

			const header = document.createElement('div');
			header.style.display = 'flex';
			header.style.alignItems = 'center';
			header.style.justifyContent = 'space-between';
			header.style.marginBottom = '5px';

			const dragHandle = document.createElement('span');
			dragHandle.className = 'dashicons dashicons-menu';
			dragHandle.style.cursor = 'move';
			dragHandle.style.color = '#8c8f94';

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'button button-link-delete';
			removeBtn.innerHTML = '<span class="dashicons dashicons-no-alt"></span>';
			removeBtn.style.padding = '0';
			removeBtn.style.minHeight = 'auto';
			removeBtn.addEventListener('click', () => { itemEl.remove(); saveVideoData(); });

			header.appendChild(dragHandle);
			header.appendChild(removeBtn);

			const mainContent = document.createElement('div');
			mainContent.style.display = 'flex';
			mainContent.style.gap = '15px';
			mainContent.style.alignItems = 'flex-start';

			const preview = document.createElement('div');
			preview.className = 'rpa-video-thumb-preview';
			preview.style.width = '100px';
			preview.style.height = '60px';
			preview.style.border = '1px solid #dcdcde';
			preview.style.borderRadius = '4px';
			preview.style.background = '#f6f7f7';
			preview.style.display = 'flex';
			preview.style.alignItems = 'center';
			preview.style.justifyContent = 'center';
			preview.style.overflow = 'hidden';
			preview.style.cursor = 'pointer';

			const renderPreview = (url) => {
				if (url) {
					preview.innerHTML = `<img src="${url}" style="width: 100%; height: 100%; object-fit: cover;">`;
				} else {
					preview.innerHTML = '<span class="dashicons dashicons-format-image" style="color: #8c8f94; font-size: 30px; width: 30px; height: 30px;"></span>';
				}
			};
			renderPreview(item.thumbnail_url);

			const fields = document.createElement('div');
			fields.style.flex = '1';
			fields.style.display = 'flex';
			fields.style.flexDirection = 'column';
			fields.style.gap = '8px';

			// Thumbnail Label & Sparkle
			const thumbHeader = document.createElement('div');
			thumbHeader.style.display = 'flex';
			thumbHeader.style.justifyContent = 'space-between';
			thumbHeader.style.alignItems = 'center';
			thumbHeader.innerHTML = '<span style="font-weight: 500; color: #1d2327;">Thumbnail Image</span><span class="dashicons dashicons-admin-appearance" style="font-size: 14px; color: #b32d2e;"></span>';
			fields.appendChild(thumbHeader);

			const uploadBtn = document.createElement('button');
			uploadBtn.type = 'button';
			uploadBtn.className = 'button button-small';
			uploadBtn.textContent = 'Set Thumbnail';
			uploadBtn.style.alignSelf = 'flex-start';
			
			const handleUpload = () => {
				if (typeof wp === 'undefined' || !wp.media) return;
				const frame = wp.media({
					title: 'Select Video Thumbnail',
					button: { text: 'Use this image' },
					multiple: false,
					library: { type: 'image' }
				});
				frame.on('select', () => {
					const attachment = frame.state().get('selection').first().toJSON();
					thumbIdInput.value = attachment.id;
					thumbUrlInput.value = attachment.url;
					renderPreview(attachment.url);
					saveVideoData();
					setTimeout(saveVideoData, 100);
				});
				frame.open();
			};

			uploadBtn.addEventListener('click', handleUpload);
			preview.addEventListener('click', handleUpload);
			fields.appendChild(uploadBtn);

			// Video URL Label & Input
			const urlRow = document.createElement('div');
			urlRow.style.display = 'flex';
			urlRow.style.alignItems = 'center';
			urlRow.style.gap = '10px';
			urlRow.style.marginTop = '5px';

			const urlLabel = document.createElement('span');
			urlLabel.style.fontWeight = '500';
			urlLabel.style.color = '#1d2327';
			urlLabel.style.minWidth = '80px';
			urlLabel.innerHTML = 'Video URL <span class="dashicons dashicons-admin-appearance" style="font-size: 14px; color: #b32d2e;"></span>';

			const urlInput = document.createElement('input');
			urlInput.type = 'text';
			urlInput.className = 'regular-text rpa-video-url';
			urlInput.value = item.url || '';
			urlInput.placeholder = 'https://www.youtube.com/watch?v=...';
			urlInput.style.flex = '1';
			urlInput.style.margin = '0';
			urlInput.addEventListener('input', saveVideoData);

			urlRow.appendChild(urlLabel);
			urlRow.appendChild(urlInput);
			fields.appendChild(urlRow);

			const thumbIdInput = document.createElement('input');
			thumbIdInput.type = 'hidden';
			thumbIdInput.className = 'rpa-video-thumb-id';
			thumbIdInput.value = item.thumbnail_id || '';

			const thumbUrlInput = document.createElement('input');
			thumbUrlInput.type = 'hidden';
			thumbUrlInput.className = 'rpa-video-thumb-url';
			thumbUrlInput.value = item.thumbnail_url || '';

			fields.appendChild(thumbIdInput);
			fields.appendChild(thumbUrlInput);

			mainContent.appendChild(preview);
			mainContent.appendChild(fields);

			itemEl.appendChild(header);
			itemEl.appendChild(mainContent);
			return itemEl;
		};

		const renderVideoGallery = () => {
			videoList.innerHTML = '';
			videoData.forEach(item => { videoList.appendChild(createRow(item)); });

			const $ = window.jQuery;
			if ($ && $.fn && $.fn.sortable) {
				$(videoList).sortable({ items: '.rpa-video-item', handle: '.dashicons-menu', update: () => saveVideoData() });
			}
		};

		btnAddRow.addEventListener('click', () => {
			videoList.appendChild(createRow());
			saveVideoData();
		});

		renderVideoGallery();
	}

	function initVideo360GalleryManager() {
		const videoInput = document.getElementById('rpa_project_360_video_gallery_data');
		const videoList = document.getElementById('rpa-360-video-list');
		const btnAddRow = document.getElementById('rpa-360-video-add-row');

		if (!videoInput || !videoList || !btnAddRow) return;

		let videoData = [];
		try { videoData = JSON.parse(videoInput.value || '[]'); } catch (e) { videoData = []; }

		const saveVideoData = () => {
			const rows = Array.from(videoList.querySelectorAll('.rpa-360-video-item'));
			const videoData = rows.map(row => {
				const htmlInput = row.querySelector('.rpa-360-iframe-html');
				return {
					iframe_html: htmlInput ? htmlInput.value.trim() : ''
				};
			}).filter(v => v.iframe_html !== '');
			videoInput.value = JSON.stringify(videoData);
			$(videoInput).trigger('change');
		};

		const createRow = (item = { iframe_html: '' }) => {
			const itemEl = document.createElement('div');
			itemEl.className = 'rpa-360-video-item';
			itemEl.style.padding = '15px';
			itemEl.style.border = '1px solid #c3c4c7';
			itemEl.style.background = '#f6f7f7';
			itemEl.style.borderRadius = '4px';
			itemEl.style.marginBottom = '10px';

			const header = document.createElement('div');
			header.style.display = 'flex';
			header.style.justifyContent = 'space-between';
			header.style.marginBottom = '10px';

			const dragHandle = document.createElement('span');
			dragHandle.className = 'dashicons dashicons-menu';
			dragHandle.style.cursor = 'move';
			dragHandle.style.color = '#8c8f94';

			const removeBtn = document.createElement('span');
			removeBtn.className = 'dashicons dashicons-no-alt';
			removeBtn.style.cursor = 'pointer';
			removeBtn.style.color = '#d63638';
			removeBtn.addEventListener('click', () => {
				if (confirm('Remove this 360 item?')) {
					itemEl.remove();
					saveVideoData();
				}
			});

			header.appendChild(dragHandle);
			header.appendChild(removeBtn);

			const fieldGroup = document.createElement('div');
			
			const label = document.createElement('div');
			label.style.fontWeight = '500';
			label.style.marginBottom = '5px';
			label.style.color = '#1d2327';
			label.innerHTML = 'Map/360 Iframe <span class="dashicons dashicons-admin-appearance" style="font-size: 14px; color: #b32d2e;"></span>';
			
			const textarea = document.createElement('textarea');
			textarea.className = 'rpa-360-iframe-html';
			textarea.style.width = '100%';
			textarea.style.height = '100px';
			textarea.value = item.iframe_html || '';
			textarea.placeholder = 'Paste iframe code here...';
			textarea.addEventListener('input', saveVideoData);

			const hint = document.createElement('p');
			hint.className = 'description';
			hint.style.marginTop = '5px';
			hint.style.fontSize = '12px';
			hint.innerHTML = 'Paste the full iframe code. Supports tags like: <code>&lt;iframe&gt;</code>, <code>&lt;a&gt;</code>, <code>&lt;script&gt;</code> (for Matterport/Google Maps).';

			fieldGroup.appendChild(label);
			fieldGroup.appendChild(textarea);
			fieldGroup.appendChild(hint);

			itemEl.appendChild(header);
			itemEl.appendChild(fieldGroup);
			return itemEl;
		};

		const renderVideoGallery = () => {
			videoList.innerHTML = '';
			videoData.forEach(item => { videoList.appendChild(createRow(item)); });

			const $ = window.jQuery;
			if ($ && $.fn && $.fn.sortable) {
				$(videoList).sortable({ items: '.rpa-360-video-item', handle: '.dashicons-menu', update: () => saveVideoData() });
			}
		};

		btnAddRow.addEventListener('click', () => {
			videoList.appendChild(createRow());
			saveVideoData();
		});

		renderVideoGallery();
	}

	function initPhotoGalleryManager() {
		const photoInput = document.getElementById('rpa_project_photo_gallery_data');
		const photoList = document.getElementById('rpa-photo-list');
		const btnAddRow = document.getElementById('rpa-photo-add-row');

		if (!photoInput || !photoList || !btnAddRow) return;

		let photoData = [];
		try { photoData = JSON.parse(photoInput.value || '[]'); } catch (e) { photoData = []; }

		const savePhotoData = () => {
			const rows = Array.from(photoList.querySelectorAll('.rpa-photo-item'));
			photoData = rows.map(row => {
				const idInput = row.querySelector('.rpa-photo-id');
				const urlInput = row.querySelector('.rpa-photo-url');
				return {
					id: idInput ? idInput.value : '',
					url: urlInput ? urlInput.value : ''
				};
			}).filter(v => v.url !== '');
			photoInput.value = JSON.stringify(photoData);
		};

		const createRow = (item = { id: '', url: '' }) => {
			const itemEl = document.createElement('div');
			itemEl.className = 'rpa-photo-item';
			itemEl.style.display = 'flex';
			itemEl.style.alignItems = 'center';
			itemEl.style.gap = '10px';
			itemEl.style.marginBottom = '10px';
			itemEl.style.padding = '10px';
			itemEl.style.border = '1px solid #c3c4c7';
			itemEl.style.background = '#fff';
			itemEl.style.borderRadius = '4px';

			const dragHandle = document.createElement('span');
			dragHandle.className = 'dashicons dashicons-menu';
			dragHandle.style.cursor = 'move';
			dragHandle.style.color = '#8c8f94';

			const preview = document.createElement('div');
			preview.className = 'rpa-photo-preview';
			preview.style.width = '60px';
			preview.style.height = '60px';
			preview.style.border = '1px solid #dcdcde';
			preview.style.borderRadius = '4px';
			preview.style.background = '#f6f7f7';
			preview.style.display = 'flex';
			preview.style.alignItems = 'center';
			preview.style.justifyContent = 'center';
			preview.style.overflow = 'hidden';

			if (item.url) {
				const img = document.createElement('img');
				img.src = item.url;
				img.style.maxWidth = '100%';
				img.style.maxHeight = '100%';
				preview.appendChild(img);
			} else {
				preview.innerHTML = '<span class="dashicons dashicons-format-image" style="color: #8c8f94;"></span>';
			}

			const idInput = document.createElement('input');
			idInput.type = 'hidden';
			idInput.className = 'rpa-photo-id';
			idInput.value = item.id || '';

			const urlInput = document.createElement('input');
			urlInput.type = 'text';
			urlInput.className = 'regular-text rpa-photo-url';
			urlInput.value = item.url || '';
			urlInput.placeholder = 'Image URL or Upload...';
			urlInput.style.flex = '1';
			urlInput.style.margin = '0';
			urlInput.addEventListener('change', savePhotoData);
			urlInput.addEventListener('input', savePhotoData);

			const uploadBtn = document.createElement('button');
			uploadBtn.type = 'button';
			uploadBtn.className = 'button';
			uploadBtn.textContent = 'Upload';
			uploadBtn.addEventListener('click', () => {
				if (typeof wp === 'undefined' || !wp.media) return;
				const frame = wp.media({
					title: 'Select Image',
					button: { text: 'Use this image' },
					multiple: false,
					library: { type: 'image' }
				});
				frame.on('select', () => {
					const attachment = frame.state().get('selection').first().toJSON();
					idInput.value = attachment.id;
					urlInput.value = attachment.url;
					
					preview.innerHTML = '';
					const img = document.createElement('img');
					img.src = attachment.url;
					img.style.maxWidth = '100%';
					img.style.maxHeight = '100%';
					preview.appendChild(img);
					
					savePhotoData();
				});
				frame.open();
			});

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'button button-link-delete';
			removeBtn.innerHTML = '<span class="dashicons dashicons-no-alt"></span>';
			removeBtn.style.padding = '0';
			removeBtn.style.minHeight = 'auto';
			removeBtn.addEventListener('click', () => {
				itemEl.remove();
				savePhotoData();
			});

			itemEl.appendChild(dragHandle);
			itemEl.appendChild(preview);
			itemEl.appendChild(idInput);
			itemEl.appendChild(urlInput);
			itemEl.appendChild(uploadBtn);
			itemEl.appendChild(removeBtn);
			return itemEl;
		};

		const renderPhotoGallery = () => {
			photoList.innerHTML = '';
			photoData.forEach(item => {
				photoList.appendChild(createRow(item));
			});

			const $ = window.jQuery;
			if ($ && $.fn && $.fn.sortable) {
				$(photoList).sortable({
					items: '.rpa-photo-item',
					handle: '.dashicons-menu',
					update: () => savePhotoData()
				});
			}
		};

		btnAddRow.addEventListener('click', () => {
			photoList.appendChild(createRow());
			savePhotoData();
		});

		renderPhotoGallery();
	}

	function initDealEntryActions() {
		const resendBtn = document.getElementById('rpa-resend-magic-link');
		if (!resendBtn) return;

		resendBtn.addEventListener('mouseenter', function() { if (!this.disabled) this.style.backgroundColor = '#2563eb'; });
		resendBtn.addEventListener('mouseleave', function() { if (!this.disabled) this.style.backgroundColor = '#3b82f6'; });

		resendBtn.addEventListener('click', function (e) {
			e.preventDefault();
			const entryId = this.getAttribute('data-entry-id');
			const msgEl = document.getElementById('rpa-resend-msg');

			this.disabled = true;
			this.style.opacity = '0.7';
			this.style.cursor = 'not-allowed';
			this.innerHTML = '<svg class="rpa-spinner" viewBox="0 0 50 50" style="width: 14px; height: 14px; animation: rpa-spin 1s linear infinite;"><circle cx="25" cy="25" r="20" fill="none" stroke="currentColor" stroke-width="5" stroke-dasharray="31.4 31.4" style="stroke-linecap: round;"></circle></svg> Sending...';
			msgEl.textContent = '';

			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: { action: 'rpa_resend_magic_link', security: window.rpaListingsAdmin ? window.rpaListingsAdmin.nonce : '', entry_id: entryId },
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
					resendBtn.innerHTML = 'Send Email';
					msgEl.style.color = '#dc2626';
					msgEl.textContent = 'Server error.';
				}
			});
		});
	}

	function initInvestmentHighlights() {
		const hiddenInput = document.getElementById('rpa_project_investment_highlights_data');
		const highlightsList = document.getElementById('rpa-highlights-list');
		const btnAddRow = document.getElementById('rpa-highlight-add-row');

		if (!hiddenInput || !highlightsList || !btnAddRow) return;

		let highlightsData = [];
		try { highlightsData = JSON.parse(hiddenInput.value || '[]'); } catch (e) { highlightsData = []; }

		const syncHighlights = () => {
			const rows = Array.from(highlightsList.querySelectorAll('.rpa-highlight-slot'));
			highlightsData = rows.map(row => {
				const iconInput = row.querySelector('.rpa-highlight-icon');
				const headlineInput = row.querySelector('.rpa-highlight-headline');
				const descInput = row.querySelector('.rpa-highlight-desc');
				return {
					icon: iconInput ? iconInput.value.trim() : '',
					headline: headlineInput ? headlineInput.value.trim() : '',
					description: descInput ? descInput.value.trim() : ''
				};
			});
			hiddenInput.value = JSON.stringify(highlightsData);
		};

		const createRow = (item = { icon: '', headline: '', description: '' }, index) => {
			const itemEl = document.createElement('div');
			itemEl.className = 'rpa-highlight-slot';
			itemEl.style.marginBottom = '15px';
			itemEl.style.padding = '15px';
			itemEl.style.border = '1px solid #c3c4c7';
			itemEl.style.background = '#fff';
			itemEl.style.position = 'relative';

			const headerDiv = document.createElement('div');
			headerDiv.style.display = 'flex';
			headerDiv.style.justifyContent = 'space-between';
			headerDiv.style.alignItems = 'center';
			headerDiv.style.marginBottom = '10px';

			const dragHandle = document.createElement('span');
			dragHandle.className = 'dashicons dashicons-menu';
			dragHandle.style.cursor = 'move';
			dragHandle.style.color = '#8c8f94';
			dragHandle.style.marginRight = '10px';

			const titleLabel = document.createElement('label');
			titleLabel.style.fontWeight = 'bold';
			titleLabel.textContent = 'Highlight';
			
			const titleWrapper = document.createElement('div');
			titleWrapper.style.display = 'flex';
			titleWrapper.style.alignItems = 'center';
			titleWrapper.appendChild(dragHandle);
			titleWrapper.appendChild(titleLabel);

			const removeBtn = document.createElement('button');
			removeBtn.type = 'button';
			removeBtn.className = 'button button-link-delete';
			removeBtn.innerHTML = '<span class="dashicons dashicons-trash"></span>';
			removeBtn.style.color = '#d63638';
			removeBtn.addEventListener('click', () => {
				itemEl.remove();
				syncHighlights();
			});

			headerDiv.appendChild(titleWrapper);
			headerDiv.appendChild(removeBtn);

			// Icon Input
			const iconRow = document.createElement('div');
			iconRow.className = 'rpa-row';
			iconRow.style.marginBottom = '8px';
			iconRow.style.display = 'none'; // Hidden as per request
			
			const iconInput = document.createElement('input');
			iconInput.type = 'text';
			iconInput.className = 'regular-text rpa-highlight-icon';
			iconInput.value = item.icon || '';
			iconInput.placeholder = 'Icon Class (e.g. fas fa-star)';
			iconInput.style.width = '100%';
			iconInput.addEventListener('input', syncHighlights);
			iconRow.appendChild(iconInput);

			// Headline Input
			const headlineRow = document.createElement('div');
			headlineRow.className = 'rpa-row';
			headlineRow.style.marginBottom = '8px';
			
			const headlineInput = document.createElement('input');
			headlineInput.type = 'text';
			headlineInput.className = 'regular-text rpa-highlight-headline';
			headlineInput.value = item.headline || '';
			headlineInput.placeholder = 'Headline';
			headlineInput.style.width = '100%';
			headlineInput.addEventListener('input', syncHighlights);
			headlineRow.appendChild(headlineInput);

			// Description Input
			const descRow = document.createElement('div');
			descRow.className = 'rpa-row';
			
			const descInput = document.createElement('textarea');
			descInput.className = 'large-text rpa-highlight-desc';
			descInput.rows = 3;
			descInput.value = item.description || '';
			descInput.placeholder = 'Description';
			descInput.style.width = '100%';
			descInput.addEventListener('input', syncHighlights);
			descRow.appendChild(descInput);

			itemEl.appendChild(headerDiv);
			itemEl.appendChild(iconRow);
			itemEl.appendChild(headlineRow);
			itemEl.appendChild(descRow);

			return itemEl;
		};

		const renderHighlights = () => {
			highlightsList.innerHTML = '';
			highlightsData.forEach((item, index) => {
				highlightsList.appendChild(createRow(item, index));
			});

			const $ = window.jQuery;
			if ($ && $.fn && $.fn.sortable) {
				$(highlightsList).sortable({
					items: '.rpa-highlight-slot',
					handle: '.dashicons-menu',
					update: () => syncHighlights()
				});
			}
		};

		btnAddRow.addEventListener('click', () => {
			highlightsList.appendChild(createRow({ icon: '', headline: '', description: '' }, highlightsList.children.length));
			syncHighlights();
		});

		renderHighlights();
	}

	function initAddressRepeater() {
		const maxAddresses = 5;
		const container = document.getElementById('rpa-addresses-container');
		const addButton = document.getElementById('rpa-add-address');

		if (!container || !addButton) return;

		const updateAddButton = () => {
			if (container.querySelectorAll('.rpa-address-item').length >= maxAddresses) {
				addButton.style.display = 'none';
			} else {
				addButton.style.display = 'inline-block';
			}
		};

		addButton.addEventListener('click', (e) => {
			e.preventDefault();
			if (container.querySelectorAll('.rpa-address-item').length < maxAddresses) {
				const item = document.createElement('div');
				item.className = 'rpa-address-item';
				item.style.marginBottom = '10px';
				item.style.display = 'flex';
				item.style.alignItems = 'center';
				item.style.gap = '10px';

				const input = document.createElement('input');
				input.type = 'text';
				input.name = 'rpa_project_addresses[]';
				input.className = 'large-text';
				input.style.width = '80%';
				input.placeholder = 'Enter additional address';

				const removeBtn = document.createElement('button');
				removeBtn.type = 'button';
				removeBtn.className = 'button rpa-remove-address';
				removeBtn.style.color = 'red';
				removeBtn.innerHTML = '&times;';

				item.appendChild(input);
				item.appendChild(removeBtn);
				container.appendChild(item);

				updateAddButton();
			}
		});

		container.addEventListener('click', (e) => {
			if (e.target.classList.contains('rpa-remove-address')) {
				e.preventDefault();
				e.target.closest('.rpa-address-item').remove();
				updateAddButton();
			}
		});

		updateAddButton();
	}

	// --- Initialization ---

	onReady(() => {
		initPhotoGalleryManager();
		initSingleUploads();
		initSelect2();
		initDocumentManager();
		initVideoGalleryManager();
		initVideo360GalleryManager();
		initInvestmentHighlights();
		initDealEntryActions();
		initAddressRepeater();
	});

})();
