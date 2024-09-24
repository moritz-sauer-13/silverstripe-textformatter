<?php

namespace TextFormatter{

    use SilverStripe\Core\Extensible;
    use Psr\SimpleCache\CacheInterface;
    use SilverStripe\Dev\Debug;
    use SilverStripe\Forms\Tip;
    use SilverStripe\ORM\ArrayList;
    use SilverStripe\ORM\FieldType\DBField;
    use SilverStripe\View\ArrayData;

    class TextFormatter
    {
        use Extensible;

        private $tags;
        private $cache;

        public function __construct(CacheInterface $cache = null)
        {
            $this->cache = $cache;
            $this->tags = $this->loadTags();
        }

        private function loadTags()
        {
            if ($this->cache) {
                $cachedTags = $this->cache->get('replaceableTags');
                if ($cachedTags) {
                    return $cachedTags;
                }
            }

            $tags = $this->replaceableTags();

            if ($this->cache) {
                $this->cache->set('replaceableTags', $tags);
            }

            return $tags;
        }

        protected function replaceableTags() {
            $tags =  [
                'Bold' => [
                    'Title' => _t(__CLASS__ . '.BOLD_TITLE', 'Fettung'),
                    'Description' => _t(__CLASS__ . '.BOLD_DESCRIPTION', 'Formatiert den Text Fett'),
                    'OpeningTag' => '[b]',
                    'ClosingTag' => '[/b]',
                    'FrontendOpeningTag' => '<strong>',
                    'FrontendClosingTag' => '</strong>',
                ],
                'Color' => [
                    'Title' => _t(__CLASS__ . '.COLOR_TITLE', 'Primärfarbe'),
                    'Description' => _t(__CLASS__ . '.COLOR_DESCRIPTION', 'Ändert die Textfarbe zur Primärfarbe'),
                    'OpeningTag' => '[c]',
                    'ClosingTag' => '[/c]',
                    'FrontendOpeningTag' => '<span class="clr-primary">',
                    'FrontendClosingTag' => '</span>',
                ],
                'OptionalLineBreak' => [
                    'Title' => _t(__CLASS__ . '.OPTIONAL_LINE_BREAK_TITLE', 'Soll Umbruchstelle'),
                    'Description' => _t(__CLASS__ . '.OPTIONAL_LINE_BREAK_DESCRIPTION', 'Hier wird der Text optional umgebrochen, wenn das Wort zu lang ist.'),
                    'OpeningTag' => '|',
                    'FrontendOpeningTag' => '&shy;',
                ],
                'LineBreak' => [
                    'Title' => _t(__CLASS__ . '.LINE_BREAK_TITLE', 'Zeilenumbruch'),
                    'Description' => _t(__CLASS__ . '.LINE_BREAK_DESCRIPTION', 'Hier wird der Text immer umgebrochen.'),
                    'OpeningTag' => '[br]',
                    'FrontendOpeningTag' => '<br>',
                ],
            ];
            $this->extend('updateReplaceableTags', $tags);
            return $tags;
        }

        private function validateTags($text)
        {
            foreach ($this->tags as $tag) {
                $openTagCount = substr_count($text, $tag['OpeningTag']);
                $closeTagCount = isset($tag['ClosingTag']) ? substr_count($text, $tag['ClosingTag']) : $openTagCount;
                if ($openTagCount !== $closeTagCount) {
                    throw new \InvalidArgumentException('Mismatched tags: ' . $tag['OpeningTag'] . (isset($tag['ClosingTag']) ? ' and ' . $tag['ClosingTag'] : ''));
                }
            }
        }

        private function FrontendFormatter($text)
        {
            $this->validateTags($text);

            foreach ($this->tags as $key => $value) {
                $openingTag = $value['OpeningTag'];
                $closingTag = $value['ClosingTag'] ?? '';
                $frontendOpeningTag = $value['FrontendOpeningTag'];
                $frontendClosingTag = $value['FrontendClosingTag'] ?? '';

                $text = str_replace($openingTag, $frontendOpeningTag, $text);
                if ($closingTag) {
                    $text = str_replace($closingTag, $frontendClosingTag, $text);
                }
            }

            return $text;
        }

        public static function formattedText($text, CacheInterface $cache = null)
        {
            $instance = new self($cache);
            $formattedText = $instance->FrontendFormatter($text);
            return DBField::create_field('HTMLText', $formattedText); // Rückgabe als HTMLText
        }

        public static function cleanedText($text)
        {
            if(!$text){
                return '';
            }
            $instance = new self();

            foreach ($instance->tags as $key => $value) {
                $openingTag = $value['OpeningTag'];
                $closingTag = $value['ClosingTag'] ?? '';

                // Entferne die Öffnungs-Tags
                $text = str_replace($openingTag, '', $text);

                // Entferne die Schließungs-Tags, falls vorhanden
                if ($closingTag) {
                    $text = str_replace($closingTag, '', $text);
                }
            }

            return $text;
        }

        public static function getFormattingDescription($keys = [], CacheInterface $cache = null)
        {
            $instance = new self($cache);

            if (!empty($keys)) {
                $filteredTags = array_filter($instance->tags, function($value, $key) use ($keys) {
                    return in_array($key, $keys);
                }, ARRAY_FILTER_USE_BOTH);
            } else {
                $filteredTags = $instance->tags;
            }

            $tags = ArrayList::create();
            foreach ($filteredTags as $key => $value) {
                $tags->push(ArrayData::create($value));
            }
            $data = [
                'ID' => uniqid(),
                'Description' => _t(__CLASS__ . '.DESCRIPTION', 'Die folgenden Formatierungen können im Text verwendet werden:'),
                'Tags' => $tags
            ];

            return (new \SilverStripe\View\ArrayData($data))->renderWith('TextFormatterDescription');
        }

        public static function getFormattingTip(){
            $description = self::getFormattingDescription();
            return new Tip($description, Tip::IMPORTANCE_LEVELS['NORMAL']);
        }
    }
}
