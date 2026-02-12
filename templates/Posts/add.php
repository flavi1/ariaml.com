<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Post $post
 * @var array $parentPosts
 * @var string $defaultLang
 * @var array $secondaryLangs
 */
?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.7.0/tinymce.min.js"></script>
<script src="https://unpkg.com/turndown/dist/turndown.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
<style>
.editor-toolbar button {
    color: black;
    font-size: inherit;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
<script src="/js/editor-by-format.js"></script>

<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Posts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="posts form content">
            <?= $this->Form->create($post) ?>
            <fieldset>
                <legend><?= __('Add New Node') ?></legend>
                
                <h3>Structure & Méta</h3>
                <?php
                    echo $this->Form->control('parent_id', [
                        'options' => $parentPosts, 
                        'empty' => '(Root)',
                        'label' => 'Parent Node'
                    ]);
                    echo $this->Form->control('type', ['options' => ['page' => 'Page', 'article' => 'Article']]);
                    echo $this->Form->control('format', ['options' => ['html' => 'HTML', 'markdown' => 'Markdown']]);
                    echo $this->Form->control('published', ['checked' => true]);
                ?>

                <hr>
                <h3><?= __('Main Content ({0})', strtoupper($defaultLang)) ?></h3>
                <?php
                    echo $this->Form->control('title');
                    echo $this->Form->control('slug', ['placeholder' => 'auto-generated-if-empty']);
                    echo $this->Form->control('description');
                    echo $this->Form->control('body', ['class' => 'editor-body', 'type' => 'textarea', 'rows' => 5]);
                ?>

                <?php foreach ($secondaryLangs as $lang): ?>
                    <hr>
                    <h3><?= __('Translation : {0}', strtoupper($lang)) ?></h3>
                    <?php
                        echo $this->Form->control("_translations.{$lang}.title", ['label' => "Title ($lang)"]);
                        echo $this->Form->control("_translations.{$lang}.slug", ['label' => "Slug ($lang)"]);
                        echo $this->Form->control("_translations.{$lang}.description", ['label' => "Description ($lang)"]);
                        echo $this->Form->control("_translations.{$lang}.body", [
							'class' => 'editor-body',
                            'label' => "Body ($lang)", 
                            'type' => 'textarea', 
                            'rows' => 5
                        ]);
                    ?>
                <?php endforeach; ?>
            </fieldset>
            <?= $this->Form->button(__('Create Node')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
