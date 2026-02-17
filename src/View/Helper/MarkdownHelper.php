<?php
namespace App\View\Helper;

use Cake\View\Helper;
use Parsedown;

class MarkdownHelper extends Helper
{
    protected Parsedown $parser;

    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->parser = new Parsedown();
        // Optionnel : Désactive l'interprétation du HTML brut pour plus de sécurité
        $this->parser->setSafeMode(false); 
    }

    /**
     * Transforme le texte Markdown en HTML
     */
    public function render(?string $text): string
    {
        if (!$text) {
            return '';
        }
        return $this->parser->text($text);
    }
}
