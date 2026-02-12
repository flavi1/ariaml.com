/**
 * CmsDashboard - Editor Logic
 * Gestion de la conversion bidirectionnelle HTML <-> Markdown
 */
const CmsEditor = {
    editors: {},
    // Liste restreinte pour garantir une conversion propre et réversible
    allowedTags: ['p', 'br', 'strong', 'em', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'a', 'code', 'pre'],
    
    turndownService: new TurndownService({
        headingStyle: 'atx',
        codeBlockStyle: 'fenced'
    }),

    /**
     * Initialisation du contrôleur de format
     */
    init() {
        const formatSelect = document.getElementById('format');
        if (!formatSelect) return;

        formatSelect.dataset.previous = formatSelect.value;

        formatSelect.addEventListener('change', (e) => {
            this.handleFormatChange(e);
        });

        this.refreshEditors(formatSelect.value);
    },

    /**
     * Gestionnaire d'événement pour le changement de format
     */
    handleFormatChange(e) {
        const oldFormat = e.target.dataset.previous;
        const newFormat = e.target.value;
        
        if (confirm(`Convertir tout le contenu vers le format ${newFormat.toUpperCase()} ?`)) {
            this.performConversion(oldFormat, newFormat);
            e.target.dataset.previous = newFormat;
        } else {
            e.target.value = oldFormat;
        }
    },

    /**
     * Conversion de la donnée pour tous les champs body
     */
    performConversion(oldFormat, newFormat) {
        const textareas = document.querySelectorAll('.editor-body');
        
        textareas.forEach(textarea => {
            let content = this.getCurrentContent(textarea.id);

            if (oldFormat === 'html' && newFormat === 'markdown') {
                content = this.toMarkdown(content);
            } else if (oldFormat === 'markdown' && newFormat === 'html') {
                content = this.toHtml(content);
            }

            this.setNewContent(textarea, content);
        });

        this.refreshEditors(newFormat);
    },

    /**
     * (Re)chargement des instances d'éditeurs
     */
    refreshEditors(format) {
        const textareas = document.querySelectorAll('.editor-body');
        
        textareas.forEach(textarea => {
            // Nettoyage Markdown (EasyMDE)
            if (this.editors[textarea.id]) {
                this.editors[textarea.id].toTextArea();
                delete this.editors[textarea.id];
            }
            
            // Nettoyage HTML (TinyMCE)
            if (typeof tinymce !== 'undefined') {
                tinymce.remove(`#${textarea.id}`);
            }

            if (format === 'markdown') {
                this.initMarkdownEditor(textarea);
            } else {
                this.initHtmlEditor(textarea);
            }
        });
    },

    /**
     * Logique spécifique aux types d'éditeurs
     */
    initMarkdownEditor(el) {
        this.editors[el.id] = new EasyMDE({
            element: el,
            forceSync: true,
            spellChecker: false,
            status: false
        });
    },

    initHtmlEditor(el) {
        if (typeof tinymce === 'undefined') return;

        tinymce.init({
            target: el,
            menubar: false,
            branding: false,
            promotion: false,
            // Restriction des balises TinyMCE
            valid_elements: this.allowedTags.join(','),
            // Restriction de la Toolbar selon allowedTags
            toolbar: 'undo redo | h1 h2 h3 | bold italic | bullist numlist | link code',
            plugins: 'lists link code',
            setup: (editor) => {
                editor.on('change blur', () => {
                    editor.save(); // Synchronise avec le textarea natif
                });
            }
        });
    },

    /**
     * Méthodes de transformation
     */
    toMarkdown(html) {
        return this.turndownService.turndown(this.sanitizeHtml(html));
    },

    toHtml(md) {
        return this.sanitizeHtml(marked.parse(md));
    },

    /**
     * Nettoyage strict du HTML via le DOM
     */
    sanitizeHtml(html) {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        
        const allElements = temp.querySelectorAll('*');
        allElements.forEach(el => {
            const tagName = el.tagName.toLowerCase();
            if (!this.allowedTags.includes(tagName)) {
                while (el.firstChild) el.parentNode.insertBefore(el.firstChild, el);
                el.parentNode.removeChild(el);
            } else {
                Array.from(el.attributes).forEach(attr => {
                    if (attr.name !== 'href' && attr.name !== 'src') {
                        el.removeAttribute(attr.name);
                    }
                });
            }
        });
        return temp.innerHTML;
    },

    /**
     * Getters/Setters pour l'abstraction de l'éditeur
     */
    getCurrentContent(id) {
        // Vérification si TinyMCE est actif pour cet ID
        const tinymceEditor = typeof tinymce !== 'undefined' ? tinymce.get(id) : null;
        if (tinymceEditor) {
            return tinymceEditor.getContent();
        }
        // Sinon EasyMDE ou textarea natif
        return this.editors[id] ? this.editors[id].value() : document.getElementById(id).value;
    },

    setNewContent(textarea, content) {
        // On met à jour le textarea brut en premier
        textarea.value = content;
        
        // Si EasyMDE est déjà réinitialisé (via refreshEditors), on met à jour sa vue
        if (this.editors[textarea.id]) {
            this.editors[textarea.id].value(content);
        }
    }
};

document.addEventListener('DOMContentLoaded', () => CmsEditor.init());
