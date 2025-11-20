/**
 * GrapesJS RSS Plus Plugin
 * Dynamically creates one block per RSS feed from database
 */
(function() {
    'use strict';

    console.log('RSS Plus: Script loaded, initializing...');

    // RSS Plus Plugin for GrapesJS
    const rssPlusPlugin = function(editor, opts = {}) {
        let currentFeedId = null;
        let rssData = null;
        let htmlTemplate = '';
        let isModalOpen = false;
        let currentPlaceholder = null;
        let availableTemplates = [];
        let availableFeeds = [];

        // Fetch feeds list from API and register blocks
        async function loadAndRegisterFeeds() {
            try {
                const response = await fetch(window.location.origin + '/rssplus/feeds/list', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success && data.feeds && data.feeds.length > 0) {
                    console.log('RSS Plus: Found', data.feeds.length, 'feeds');

                    // Store feeds for later use
                    availableFeeds = data.feeds;

                    // Register a block for each feed
                    data.feeds.forEach(function(feed) {
                        registerFeedBlock(feed);
                    });
                } else {
                    console.log('RSS Plus: No feeds found');
                    availableFeeds = [];
                }
            } catch (error) {
                console.error('RSS Plus: Error loading feeds:', error);
            }
        }

        // Register a single feed block
        function registerFeedBlock(feed) {
            const blockId = 'rssplus-feed-' + feed.id;
            const componentType = blockId + '-placeholder';

            // Define component type for this feed
            editor.DomComponents.addType(componentType, {
                model: {
                    defaults: {
                        tagName: 'div',
                        draggable: true,
                        droppable: false,
                        removable: true,
                        copyable: false,
                        attributes: {
                            'data-rssplus-placeholder': 'true',
                            'data-feed-id': feed.id
                        },
                        content: '<p style="text-align: center; padding: 20px; background: #f0f0f0; border: 2px dashed #ccc;">Loading ' + feed.name + '...</p>',
                    }
                }
            });

            // Add block to BlockManager
            editor.BlockManager.add(blockId, {
                label: feed.name,
                category: 'RSS Plus Feeds',
                content: {
                    type: componentType,
                    feedId: feed.id
                },
                media: '<i class="fa fa-rss" style="font-size: 32px; color: #ff6600;"></i>',
                attributes: {
                    title: 'Drag to import ' + feed.name + ' RSS items',
                    class: 'rssplus-feed-block'
                }
            });

            console.log('RSS Plus: Registered block for', feed.name);
        }

        // Register RSS Plus Token block
        function registerTokenBlock() {
            const componentType = 'rssplus-token-placeholder';

            // Define component type for token
            editor.DomComponents.addType(componentType, {
                model: {
                    defaults: {
                        tagName: 'div',
                        draggable: true,
                        droppable: false,
                        removable: true,
                        copyable: false,
                        attributes: {
                            'data-rssplus-token-placeholder': 'true'
                        },
                        content: '<p style="text-align: center; padding: 20px; background: #e8f5e9; border: 2px dashed #4caf50;">RSS Plus Token Configuration</p>',
                    }
                }
            });

            // Add block to BlockManager in Extra category
            editor.BlockManager.add('rssplus-token', {
                label: 'RSS Plus Token',
                category: 'Extra',
                content: {
                    type: componentType
                },
                media: '<i class="fa fa-code" style="font-size: 32px; color: #4caf50;"></i>',
                attributes: {
                    title: 'Insert RSS Plus Token',
                    class: 'rssplus-token-block'
                }
            });

            console.log('RSS Plus: Registered token block');
        }

        // Listen for component added event
        editor.on('component:add', function(component) {
            const attrs = component.getAttributes();

            if (attrs['data-rssplus-placeholder'] && !isModalOpen) {
                isModalOpen = true;
                currentPlaceholder = component;
                currentFeedId = parseInt(attrs['data-feed-id']);

                console.log('RSS Plus: Feed block added, fetching items for feed', currentFeedId);

                setTimeout(function() {
                    fetchFeedAndShowModal(currentFeedId);
                }, 100);
            }

            if (attrs['data-rssplus-token-placeholder'] && !isModalOpen) {
                isModalOpen = true;
                currentPlaceholder = component;

                console.log('RSS Plus: Token block added, showing configuration modal');

                setTimeout(function() {
                    showTokenConfigModal();
                }, 100);
            }
        });

        // Fetch templates list
        async function fetchTemplates() {
            try {
                const response = await fetch(window.location.origin + '/rssplus/templates/list', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (data.success && data.templates) {
                    availableTemplates = data.templates;
                    console.log('RSS Plus: Loaded', availableTemplates.length, 'templates');
                } else {
                    console.warn('RSS Plus: No templates found');
                    availableTemplates = [];
                }
            } catch (error) {
                console.error('RSS Plus: Error loading templates:', error);
                availableTemplates = [];
            }
        }

        // Fetch RSS items for specific feed
        async function fetchFeedAndShowModal(feedId) {
            showLoadingModal();

            // Fetch templates if not already loaded
            if (availableTemplates.length === 0) {
                await fetchTemplates();
            }

            try {
                const fetchUrl = window.location.origin + '/rssplus/rss/fetch/' + feedId;
                console.log('RSS Plus: Fetching from:', fetchUrl);

                const response = await fetch(fetchUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                const data = await response.json();

                if (!data.success) {
                    alert('Error fetching RSS feed: ' + (data.error || 'Unknown error'));
                    closeModal();
                    return;
                }

                rssData = data.items;
                htmlTemplate = data.template;
                showRssModal(data.items, data.feedName, editor);
            } catch (error) {
                alert('Error: ' + error.message);
                closeModal();
            }
        }

        function showLoadingModal() {
            const modalHtml = `
                <div class="modal fade in" id="rssplus-modal" style="display: block; z-index: 10000;">
                    <div class="modal-backdrop fade in" style="z-index: 9999;"></div>
                    <div class="modal-dialog modal-lg" style="z-index: 10001;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" id="loading-modal-close-btn">
                                    <span>&times;</span>
                                </button>
                                <h4 class="modal-title">RSS Plus Feed Import</h4>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <i class="fa fa-spinner fa-spin fa-3x"></i>
                                    <p class="mt-3">Loading RSS feed...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHtml;
            document.body.appendChild(modalContainer.firstElementChild);

            setTimeout(function() {
                const closeBtn = document.getElementById('loading-modal-close-btn');
                if (closeBtn) {
                    closeBtn.addEventListener('click', closeModal);
                }
            }, 0);
        }

        function showRssModal(items, feedName, editor) {
            if (!items || items.length === 0) {
                alert('No items found in RSS feed');
                closeModal();
                return;
            }

            // Generate template options HTML
            const templateOptions = availableTemplates.map(function(template, idx) {
                // Select first template by default
                const selected = idx === 0 ? 'selected' : '';
                return `<option value="${template.id}" ${selected}>${template.name}</option>`;
            }).join('');

            const itemsHtml = items.map(function(item, index) {
                const title = item.title || 'Untitled';
                let description = item.description || '';
                const pubDate = item.pubDate || '';

                if (description.length > 150) {
                    description = description.substring(0, 150) + '...';
                }
                description = description.replace(/<[^>]*>/g, '');

                return `
                    <div class="rss-item" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 10px; border-radius: 4px; display: flex; align-items: center; gap: 15px;">
                        <div style="flex: 1;">
                            <label style="display: block; cursor: pointer; margin: 0;">
                                <input type="checkbox" class="rss-item-checkbox" value="${index}" style="margin-right: 10px;">
                                <strong>${title}</strong>
                                ${pubDate ? '<small class="text-muted" style="margin-left: 10px;">' + pubDate + '</small>' : ''}
                                ${description ? '<div style="margin-top: 5px; margin-left: 25px; color: #666;">' + description + '</div>' : ''}
                            </label>
                        </div>
                        <div style="min-width: 200px;">
                            <select class="rss-item-template form-control" data-item-index="${index}" style="width: 100%;">
                                ${templateOptions}
                            </select>
                        </div>
                    </div>
                `;
            }).join('');

            const modalHtml = `
                <div class="modal fade in" id="rssplus-modal" style="display: block; z-index: 10000;">
                    <div class="modal-backdrop fade in" style="z-index: 9999;"></div>
                    <div class="modal-dialog modal-lg" style="z-index: 10001;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" id="modal-close-btn">
                                    <span>&times;</span>
                                </button>
                                <h4 class="modal-title">Select Items from ${feedName}</h4>
                            </div>
                            <div class="modal-body" style="max-height: 500px; overflow-y: auto;">
                                <div class="mb-3">
                                    <button type="button" class="btn btn-default btn-sm" id="select-all-items">Select All</button>
                                    <button type="button" class="btn btn-default btn-sm" id="deselect-all-items">Deselect All</button>
                                </div>
                                <div id="rss-items-list">
                                    ${itemsHtml}
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" id="modal-cancel-btn">Cancel</button>
                                <button type="button" class="btn btn-primary" id="insert-rss-items">Insert Selected Items</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const existingModal = document.getElementById('rssplus-modal');
            if (existingModal) {
                existingModal.remove();
            }

            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHtml;
            document.body.appendChild(modalContainer.firstElementChild);

            document.getElementById('modal-close-btn').addEventListener('click', closeModal);
            document.getElementById('modal-cancel-btn').addEventListener('click', closeModal);

            document.getElementById('select-all-items').addEventListener('click', function() {
                document.querySelectorAll('.rss-item-checkbox').forEach(function(cb) {
                    cb.checked = true;
                });
            });

            document.getElementById('deselect-all-items').addEventListener('click', function() {
                document.querySelectorAll('.rss-item-checkbox').forEach(function(cb) {
                    cb.checked = false;
                });
            });

            document.getElementById('insert-rss-items').addEventListener('click', function() {
                insertSelectedItems(editor);
            });
        }

        function insertSelectedItems(editor) {
            const checkboxes = document.querySelectorAll('.rss-item-checkbox:checked');

            if (checkboxes.length === 0) {
                alert('Please select at least one item to insert');
                return;
            }

            // Validate that all selected items have a template selected
            const selectedIndices = Array.from(checkboxes).map(function(cb) {
                return parseInt(cb.value);
            });

            for (let i = 0; i < selectedIndices.length; i++) {
                const index = selectedIndices[i];
                const templateSelect = document.querySelector('.rss-item-template[data-item-index="' + index + '"]');
                const templateId = templateSelect ? parseInt(templateSelect.value) : null;

                if (!templateId) {
                    alert('Please select a template for all selected items');
                    return;
                }
            }

            selectedIndices.sort(function(a, b) { return a - b; });

            let htmlContent = '';

            selectedIndices.forEach(function(index) {
                const item = rssData[index];

                // Get the selected template for this item
                const templateSelect = document.querySelector('.rss-item-template[data-item-index="' + index + '"]');
                const templateId = parseInt(templateSelect.value);

                // Find the template content
                const template = availableTemplates.find(function(t) { return t.id === templateId; });

                if (!template) {
                    console.error('RSS Plus: Template not found for id', templateId);
                    return;
                }

                let itemHtml = template.content;

                // Replace tokens in the template with RSS item data
                Object.keys(item).forEach(function(key) {
                    const token = '{' + key + '}';
                    const value = item[key] || '';
                    itemHtml = itemHtml.split(token).join(value);
                });

                htmlContent += itemHtml + '\n';
            });

            try {
                if (currentPlaceholder && currentPlaceholder.parent()) {
                    const parent = currentPlaceholder.parent();
                    const index = parent.components().indexOf(currentPlaceholder);

                    currentPlaceholder.remove();
                    parent.append(htmlContent, { at: index });
                    console.log('RSS Plus: Content inserted at placeholder position');
                } else {
                    const wrapper = editor.getWrapper();
                    editor.addComponents(htmlContent, { at: wrapper.components().length });
                    console.log('RSS Plus: Content inserted at end');
                }
            } catch (error) {
                console.error('RSS Plus: Error inserting content:', error);
                alert('Error inserting content: ' + error.message);
            }

            closeModal();
        }

        async function showTokenConfigModal() {
            // Ensure feeds and templates are loaded
            if (availableFeeds.length === 0) {
                await loadAndRegisterFeeds();
            }
            if (availableTemplates.length === 0) {
                await fetchTemplates();
            }

            if (availableFeeds.length === 0) {
                alert('No RSS feeds found. Please create a feed first.');
                closeModal();
                return;
            }

            if (availableTemplates.length === 0) {
                alert('No templates found. Please create a template first.');
                closeModal();
                return;
            }

            // Generate feed options
            const feedOptions = availableFeeds.map(function(feed, idx) {
                const selected = idx === 0 ? 'selected' : '';
                return `<option value="${feed.id}" ${selected}>${feed.name}</option>`;
            }).join('');

            // Generate template options
            const templateOptions = availableTemplates.map(function(template, idx) {
                const selected = idx === 0 ? 'selected' : '';
                return `<option value="${template.id}" ${selected}>${template.name}</option>`;
            }).join('');

            const modalHtml = `
                <div class="modal fade in" id="rssplus-modal" style="display: block; z-index: 10000;">
                    <div class="modal-backdrop fade in" style="z-index: 9999;"></div>
                    <div class="modal-dialog" style="z-index: 10001;">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" id="token-modal-close-btn">
                                    <span>&times;</span>
                                </button>
                                <h4 class="modal-title">Configure RSS Plus Token</h4>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="token-feed-select">Select Feed</label>
                                    <select id="token-feed-select" class="form-control">
                                        ${feedOptions}
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="token-template-select">Select Template</label>
                                    <select id="token-template-select" class="form-control">
                                        ${templateOptions}
                                    </select>
                                </div>
                                <div class="alert alert-info">
                                    <strong>Info:</strong> This will insert a token that will be replaced with RSS content when the email is sent.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" id="token-modal-cancel-btn">Cancel</button>
                                <button type="button" class="btn btn-primary" id="insert-token-btn">Insert Token</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const existingModal = document.getElementById('rssplus-modal');
            if (existingModal) {
                existingModal.remove();
            }

            const modalContainer = document.createElement('div');
            modalContainer.innerHTML = modalHtml;
            document.body.appendChild(modalContainer.firstElementChild);

            document.getElementById('token-modal-close-btn').addEventListener('click', closeModal);
            document.getElementById('token-modal-cancel-btn').addEventListener('click', closeModal);
            document.getElementById('insert-token-btn').addEventListener('click', function() {
                insertToken(editor);
            });
        }

        function insertToken(editor) {
            const feedId = document.getElementById('token-feed-select').value;
            const templateId = document.getElementById('token-template-select').value;

            if (!feedId || !templateId) {
                alert('Please select both a feed and a template');
                return;
            }

            // Create the token
            const token = `{RssPlus:feed:${feedId}:template:${templateId}}`;

            // Create the MJML section with the token
            const mjmlContent = `<mj-section> <mj-text color="#000000" font-family="" font-size="16px" line-height="1.5" font-weight="300" align="justify">\n  ${token}\n        </mj-text> </mj-section>`;

            try {
                if (currentPlaceholder && currentPlaceholder.parent()) {
                    const parent = currentPlaceholder.parent();
                    const index = parent.components().indexOf(currentPlaceholder);

                    currentPlaceholder.remove();
                    parent.append(mjmlContent, { at: index });
                    console.log('RSS Plus: Token inserted at placeholder position');
                } else {
                    const wrapper = editor.getWrapper();
                    editor.addComponents(mjmlContent, { at: wrapper.components().length });
                    console.log('RSS Plus: Token inserted at end');
                }
            } catch (error) {
                console.error('RSS Plus: Error inserting token:', error);
                alert('Error inserting token: ' + error.message);
            }

            closeModal();
        }

        function closeModal() {
            const modal = document.getElementById('rssplus-modal');
            if (modal) {
                modal.remove();
            }

            if (currentPlaceholder && currentPlaceholder.parent()) {
                currentPlaceholder.remove();
            }

            isModalOpen = false;
            currentPlaceholder = null;
            currentFeedId = null;
        }

        // Load feeds when editor is ready
        editor.on('load', function() {
            console.log('RSS Plus: Editor loaded, fetching feeds...');
            loadAndRegisterFeeds();
            registerTokenBlock();
        });
    };

    // Register plugin with Mautic GrapesJS
    if (!window.MauticGrapesJsPlugins) {
        window.MauticGrapesJsPlugins = [];
    }

    window.MauticGrapesJsPlugins.push({
        name: 'mautic-rssplus',
        plugin: rssPlusPlugin
    });

    console.log('RSS Plus: Plugin registered with Mautic GrapesJS');
})();
