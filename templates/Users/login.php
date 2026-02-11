<div class="users form">
    <?= $this->Flash->render() ?>
    <h3>Connexion au Dashboard</h3>
    <?= $this->Form->create() ?>
    <fieldset>
        <legend><?= __('Veuillez entrer votre nom d\'utilisateur et votre mot de passe') ?></legend>
        <?= $this->Form->control('username', ['required' => true]) ?>
        <?= $this->Form->control('password', ['required' => true]) ?>
    </fieldset>
    <?= $this->Form->submit(__('Se connecter')); ?>
    <?= $this->Form->end() ?>
</div>
