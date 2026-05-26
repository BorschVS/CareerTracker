/**
 * Rich Text Editor JavaScript
 * Provides Slack-like editing toolbar and functionality
 */

$(document).ready(function() {
    RichEditor.init();
});

const RichEditor = {
    
    activeEditor: null,
    
    init: function(container) {
        if (container) {
            this.initEditor(container);
        } else {
            // Initialize all editors on the page
            $('.rich-editor').each(function() {
                RichEditor.initEditor($(this));
            });
        }
        
        this.bindGlobalEvents();
    },
    
    initEditor: function(editorElement) {
        // Skip if already initialized
        if (editorElement.data('editor-initialized')) {
            return;
        }
        
        editorElement.data('editor-initialized', true);
        
        // Create toolbar
        const toolbar = this.createToolbar();
        editorElement.before(toolbar);
        
        // Bind editor events
        editorElement.on({
            'focus': () => {
                this.activeEditor = editorElement;
                toolbar.addClass('active');
                editorElement.addClass('editor-focus');
            },
            'blur': () => {
                setTimeout(() => {
                    if (!toolbar.is(':hover')) {
                        toolbar.removeClass('active');
                        editorElement.removeClass('editor-focus');
                        this.activeEditor = null;
                    }
                }, 200);
            },
            'keydown': (e) => this.handleKeydown(e),
            'paste': (e) => this.handlePaste(e),
            'input': () => this.handleInput(editorElement)
        });
        
        // Initialize content if empty
        if (!editorElement.html().trim() || editorElement.text() === 'Start typing...') {
            editorElement.html('<p><br></p>');
        }
        
        // Store reference
        editorElement.data('toolbar', toolbar);
    },
    
    createToolbar: function() {
        const toolbar = $(`
            <div class="editor-toolbar glass-card">
                <div class="toolbar-group">
                    <button class="toolbar-btn" data-command="bold" title="Bold (Ctrl+B)">
                        <i class="icon-bold"></i>
                    </button>
                    <button class="toolbar-btn" data-command="italic" title="Italic (Ctrl+I)">
                        <i class="icon-italic"></i>
                    </button>
                    <button class="toolbar-btn" data-command="underline" title="Underline (Ctrl+U)">
                        <i class="icon-underline"></i>
                    </button>
                    <button class="toolbar-btn" data-command="strikethrough" title="Strikethrough">
                        <i class="icon-strikethrough"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button class="toolbar-btn" data-command="insertUnorderedList" title="Bullet List">
                        <i class="icon-list-ul"></i>
                    </button>
                    <button class="toolbar-btn" data-command="insertOrderedList" title="Numbered List">
                        <i class="icon-list-ol"></i>
                    </button>
                    <button class="toolbar-btn" data-command="blockquote" title="Quote">
                        <i class="icon-quote"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button class="toolbar-btn" data-command="code" title="Inline Code">
                        <i class="icon-code"></i>
                    </button>
                    <button class="toolbar-btn" data-command="codeblock" title="Code Block">
                        <i class="icon-code-block"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button class="toolbar-btn" data-command="link" title="Add Link">
                        <i class="icon-link"></i>
                    </button>
                    <button class="toolbar-btn" data-command="image" title="Insert Image">
                        <i class="icon-image"></i>
                    </button>
                </div>
                
                <div class="toolbar-separator"></div>
                
                <div class="toolbar-group">
                    <button class="toolbar-btn" data-command="undo" title="Undo (Ctrl+Z)">
                        <i class="icon-undo"></i>
                    </button>
                    <button class="toolbar-btn" data-command="redo" title="Redo (Ctrl+Y)">
                        <i class="icon-redo"></i>
                    </button>
                </div>
            </div>
        `);
        
        // Bind toolbar events
        toolbar.find('.toolbar-btn').on('click', (e) => {
            e.preventDefault();
            const command = $(e.currentTarget).data('command');
            this.executeCommand(command);
        });
        
        return toolbar;
    },
    
    bindGlobalEvents: function() {
        // Handle keyboard shortcuts
        $(document).on('keydown', (e) => {
            if (!this.activeEditor) return;
            
            const ctrl = e.ctrlKey || e.metaKey;
            
            if (ctrl) {
                switch(e.key.toLowerCase()) {
                    case 'b':
                        e.preventDefault();
                        this.executeCommand('bold');
                        break;
                    case 'i':
                        e.preventDefault();
                        this.executeCommand('italic');
                        break;
                    case 'u':
                        e.preventDefault();
                        this.executeCommand('underline');
                        break;
                    case 'k':
                        e.preventDefault();
                        this.executeCommand('link');
                        break;
                    case 'z':
                        if (e.shiftKey) {
                            e.preventDefault();
                            this.executeCommand('redo');
                        } else {
                            e.preventDefault();
                            this.executeCommand('undo');
                        }
                        break;
                    case 'y':
                        e.preventDefault();
                        this.executeCommand('redo');
                        break;
                }
            }
        });
    },
    
    executeCommand: function(command, value = null) {
        if (!this.activeEditor) return;
        
        this.activeEditor.focus();
        
        switch(command) {
            case 'bold':
            case 'italic':
            case 'underline':
            case 'strikethrough':
                document.execCommand(command, false, null);
                break;
                
            case 'insertUnorderedList':
            case 'insertOrderedList':
                document.execCommand(command, false, null);
                break;
                
            case 'blockquote':
                this.toggleBlockquote();
                break;
                
            case 'code':
                this.toggleInlineCode();
                break;
                
            case 'codeblock':
                this.insertCodeBlock();
                break;
                
            case 'link':
                this.insertLink();
                break;
                
            case 'image':
                this.insertImage();
                break;
                
            case 'undo':
            case 'redo':
                document.execCommand(command, false, null);
                break;
        }
        
        this.updateToolbarState();
        this.handleInput(this.activeEditor);
    },
    
    toggleBlockquote: function() {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            const blockquote = document.createElement('blockquote');
            
            try {
                range.surroundContents(blockquote);
            } catch (e) {
                blockquote.appendChild(range.extractContents());
                range.insertNode(blockquote);
            }
        }
    },
    
    toggleInlineCode: function() {
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const selectedText = selection.toString();
            if (selectedText) {
                const code = document.createElement('code');
                code.textContent = selectedText;
                
                const range = selection.getRangeAt(0);
                range.deleteContents();
                range.insertNode(code);
            }
        }
    },
    
    insertCodeBlock: function() {
        const code = prompt('Enter your code:');
        if (code !== null) {
            const language = prompt('Programming language (optional):', 'javascript') || 'text';
            
            const pre = document.createElement('pre');
            const codeElement = document.createElement('code');
            codeElement.className = `language-${language}`;
            codeElement.textContent = code;
            pre.appendChild(codeElement);
            
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                range.insertNode(pre);
                range.collapse(false);
            }
        }
    },
    
    insertLink: function() {
        const url = prompt('Enter URL:');
        if (url) {
            const text = window.getSelection().toString() || prompt('Link text:', url);
            if (text) {
                const link = `<a href="${url}" target="_blank">${text}</a>`;
                document.execCommand('insertHTML', false, link);
            }
        }
    },
    
    insertImage: function() {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        
        input.onchange = (e) => {
            const file = e.target.files[0];
            if (file) {
                this.uploadImage(file);
            }
        };
        
        input.click();
    },
    
    uploadImage: function(file) {
        // Show loading indicator
        const loadingImg = '<img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iMjAiIGN5PSIyMCIgcj0iMTgiIHN0cm9rZT0iIzMzMzMzMyIgc3Ryb2tlLXdpZHRoPSI0Ii8+CjxjaXJjbGUgY3g9IjIwIiBjeT0iMjAiIHI9IjE4IiBzdHJva2U9IiM2NjZlZWEiIHN0cm9rZS13aWR0aD0iNCIgc3Ryb2tlLWRhc2hhcnJheT0iMTAgNSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIi8+CjxhbmltYXRlVHJhbnNmb3JtIGF0dHJpYnV0ZU5hbWU9InRyYW5zZm9ybSIgdHlwZT0icm90YXRlIiB2YWx1ZXM9IjAgMjAgMjA7MzYwIDIwIDIwIiBkdXI9IjFzIiByZXBlYXRDb3VudD0iaW5kZWZpbml0ZSIvPgo8L3N2Zz4K" alt="Uploading..." style="width: 40px; height: 40px;">';
        document.execCommand('insertHTML', false, loadingImg);
        
        // Create FormData for file upload
        const formData = new FormData();
        formData.append('file', file);
        formData.append('action', 'upload_editor_image');
        formData.append('nonce', CareerTracker.nonce);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: (response) => {
                if (response.success) {
                    // Replace loading image with actual image
                    const img = `<img src="${response.data.url}" alt="${file.name}" style="max-width: 100%; height: auto;" />`;
                    
                    // Find and replace the loading image
                    const editorHtml = this.activeEditor.html();
                    const updatedHtml = editorHtml.replace(loadingImg, img);
                    this.activeEditor.html(updatedHtml);
                    
                    showNotification('Image uploaded successfully', 'success');
                } else {
                    // Remove loading image on error
                    const editorHtml = this.activeEditor.html();
                    const updatedHtml = editorHtml.replace(loadingImg, '');
                    this.activeEditor.html(updatedHtml);
                    
                    showNotification(response.data || 'Error uploading image', 'error');
                }
            },
            error: () => {
                // Remove loading image on error
                const editorHtml = this.activeEditor.html();
                const updatedHtml = editorHtml.replace(loadingImg, '');
                this.activeEditor.html(updatedHtml);
                
                showNotification('Network error uploading image', 'error');
            }
        });
    },
    
    updateToolbarState: function() {
        if (!this.activeEditor) return;
        
        const toolbar = this.activeEditor.data('toolbar');
        if (!toolbar) return;
        
        // Update button states based on current formatting
        toolbar.find('.toolbar-btn').removeClass('active');
        
        const commands = ['bold', 'italic', 'underline', 'strikethrough'];
        commands.forEach(command => {
            if (document.queryCommandState(command)) {
                toolbar.find(`[data-command="${command}"]`).addClass('active');
            }
        });
    },
    
    handleKeydown: function(e) {
        // Handle special keys
        if (e.key === 'Enter') {
            // Handle line breaks in code blocks
            const selection = window.getSelection();
            if (selection.rangeCount > 0) {
                const range = selection.getRangeAt(0);
                const container = range.commonAncestorContainer;
                
                if ($(container).closest('pre').length) {
                    e.preventDefault();
                    document.execCommand('insertHTML', false, '\n');
                }
            }
        }
    },
    
    handlePaste: function(e) {
        e.preventDefault();
        
        // Get plain text from clipboard
        const paste = (e.clipboardData || window.clipboardData).getData('text');
        
        // Insert as plain text
        document.execCommand('insertText', false, paste);
    },
    
    handleInput: function(editor) {
        // Clean up empty paragraphs and normalize content
        const html = editor.html();
        
        // Remove empty paragraphs
        const cleaned = html.replace(/<p><br><\/p>/g, '<p></p>');
        
        if (cleaned !== html) {
            editor.html(cleaned);
        }
        
        // Show save indicator
        const section = editor.closest('.project-section');
        if (section.length) {
            section.find('.save-section').show();
        }
    },
    
    getContent: function(editor) {
        return {
            html: editor.html(),
            text: editor.text()
        };
    },
    
    setContent: function(editor, content) {
        if (typeof content === 'string') {
            editor.html(content);
        } else if (content.html) {
            editor.html(content.html);
        }
    }
};

// Export for global access
window.RichEditor = RichEditor;