<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Post $post
 * @var bool $isHome
 */

// On définit le titre de la page pour le layout
$this->assign('title', $post->title);
?>

<article class="article-node <?= $isHome ? 'is-home' : '' ?>" 
         data-id="<?= $post->id ?>" 
         data-type="<?= h($post->type) ?>"
         data-format="<?= h($post->format) ?>">

    <?php if (!$isHome): ?>
    <nav class="breadcrumb">
        <?= $this->Html->link(__('Home'), '/') ?>
        <?php if ($post->has('parent_post')): ?>
            / <?= h($post->parent_post->title) ?>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <header class="node-header">
        <h1><?= h($post->title) ?></h1>
        <?php if (!empty($post->description)): ?>
            <p class="lead"><?= h($post->description) ?></p>
        <?php endif; ?>
    </header>

	<div class="node-body">
		<?php if ($post->format === 'markdown'): ?>
			<div class="markdown-content">
				<?= $this->Markdown->render($post->body) ?>
			</div>
		<?php else: ?>
			<?php // Rendu HTML direct pour AriaML ?>
			<?= $post->body ?>
		<?php endif; ?>
	</div>

    <?php if (!empty($post->child_posts)): ?>
    <footer class="node-children">
        <h3><?= __('In this section:') ?></h3>
        <ul>
            <?php foreach ($post->child_posts as $child): ?>
                <li>
                    <?= $this->Html->link($child->title, [
                        'action' => 'publicView', 
                        'lang' => $this->request->getParam('lang'),
                        $child->slug 
                    ]) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </footer>
    <?php endif; ?>
</article>

<style>
    .article-node { max-width: 800px; margin: 0 auto; padding: 2rem; }
    .node-header { margin-bottom: 2rem; border-bottom: 1px solid #eee; padding-bottom: 1rem; }
    .node-header h1 { font-size: 2.5rem; color: #2d3748; }
    .lead { font-size: 1.25rem; color: #718096; }
    .node-body { line-height: 1.6; color: #4a5568; }
    .breadcrumb { font-size: 0.9rem; color: #a0aec0; margin-bottom: 1rem; }
    .node-children { margin-top: 3rem; padding-top: 2rem; border-top: 2px dashed #edf2f7; }
</style>
