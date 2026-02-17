<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Post> $posts
 * @var mixed $currentPageId  // L'ID stocké dans la table Settings
 */
use Cake\Core\Configure;

$locales = Configure::read('App.locales');
$defaultLang = Configure::read('App.defaultLanguage');
?>
<div class="posts index content">
    <?= $this->Html->link(__('New Node'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('AriaML Document Structure') ?></h3>

    <?= $this->Form->create(null) ?>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">Home</th>
                    <th><?= $this->Paginator->sort('title', 'Structure / Title') ?></th>
                    <th>Type</th>
                    <th>Languages</th>
                    <th><?= $this->Paginator->sort('published', 'Status') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                <tr>
                    <td style="text-align: center;">
                        <?php 
                            // Radio button pour la home page
                            echo $this->Form->radio('home_page_id', 
                                [$post->id => ''], 
                                [
                                    'value' => $currentPageId, 
                                    'hiddenField' => false,
                                    'label' => false
                                ]
                            ); 
                        ?>
                    </td>
                    <td>
                        <?php 
                            $prefix = str_repeat('— ', $post->level ?? 0); 
                            echo $prefix . h($post->title); 
                        ?>
                    </td>
                    <td><small><?= h($post->type) ?> (<?= h($post->format) ?>)</small></td>
                    <td>
                        <?php foreach ($locales as $code => $name): ?>
                            <?php 
                                $isDefault = ($code === $defaultLang);
                                $hasTranslation = false;
                                // En Shadow Strategy, on vérifie la propriété injectée par le Finder translations
                                if (!$isDefault && isset($post->_translations)) {
                                    $hasTranslation = isset($post->_translations[$code]);
                                }
                                
                                $style = ($isDefault || $hasTranslation) ? 'badge-success' : 'badge-missing';
                            ?>
                            <span class="badge <?= $style ?>" title="<?= h($name) ?>">
                                <?= strtoupper($code) ?>
                            </span>
                        <?php endforeach; ?>
                    </td>
                    <td><?= $post->published ? '✅' : '⏳' ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $post->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $post->id], ['confirm' => __('Delete node {0}?', $post->title)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="form-submit-footer" style="margin-top: 20px; text-align: right;">
        <?= $this->Form->button(__('Save Home Selection'), ['class' => 'button secondary']) ?>
    </div>
    <?= $this->Form->end() ?>

    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->numbers() ?>
        </ul>
    </div>
</div>

<style>
    .badge { padding: 2px 6px; border-radius: 4px; font-size: 0.8rem; margin-right: 2px; }
    .badge-success { background: #e6fffa; color: #2c7a7b; border: 1px solid #81e6d9; }
    .badge-missing { background: #fff5f5; color: #c53030; border: 1px solid #feb2b2; opacity: 0.5; }
    /* Style pour les radios dans le tableau */
    input[type="radio"] { margin: 0; cursor: pointer; }
</style>
