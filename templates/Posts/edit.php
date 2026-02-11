<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Post $post
 * @var array $parentPosts // Ajouté via le controller
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $post->id], ['confirm' => __('Are you sure?'), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Posts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="posts form content">
            <?= $this->Form->create($post) ?>
            <fieldset>
                <legend><?= __('Edit Node') ?></legend>
                
                <h3>Structure & Méta</h3>
                <?php
                    echo $this->Form->control('parent_id', ['options' => $parentPosts, 'empty' => '(Root)']);
                    echo $this->Form->control('type', ['options' => ['page' => 'Page', 'fragment' => 'Fragment']]);
                    echo $this->Form->control('format', ['options' => ['html' => 'HTML', 'markdown' => 'Markdown']]);
                    echo $this->Form->control('published');
                ?>

                <hr>
                <h3><?= __('Contenu Principal ({0})', strtoupper($defaultLang)) ?></h3>
                <?php
                    echo $this->Form->control('title');
                    echo $this->Form->control('slug');
                    echo $this->Form->control('description');
                    echo $this->Form->control('body', ['type' => 'textarea', 'rows' => 5]);
                ?>

                <?php foreach ($secondaryLangs as $lang): ?>
                    <hr>
                    <h3><?= __('Traduction : {0}', strtoupper($lang)) ?></h3>
                    <?php
                        echo $this->Form->control("_translations.{$lang}.title", ['label' => "Title ($lang)"]);
                        echo $this->Form->control("_translations.{$lang}.slug", ['label' => "Slug ($lang)"]);
                        echo $this->Form->control("_translations.{$lang}.description", ['label' => "Description ($lang)"]);
                        echo $this->Form->control("_translations.{$lang}.body", [
                            'label' => "Body ($lang)", 
                            'type' => 'textarea', 
                            'rows' => 5
                        ]);
                    ?>
                <?php endforeach; ?>
            </fieldset>
            <?= $this->Form->button(__('Save')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
