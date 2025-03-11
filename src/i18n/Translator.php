<?php
declare (strict_types = 1);
/**
 * This file is part of Scaleum Framework.
 *
 * (C) 2009-2025 Maxim Kirichenko <kirichenko.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Scaleum\i18n;

use Scaleum\i18n\Contracts\LocaleIdentityInterface;
use Scaleum\i18n\Loaders\TranslationLoaderAbstract;
use Scaleum\Stdlib\Base\FileResolver;
use Scaleum\Stdlib\Base\Hydrator;
use Scaleum\Stdlib\Exceptions\ERuntimeError;

class Translator extends Hydrator {

    protected array $files = [];

    protected ?LoaderDispatcher $loaderDispatcher = null;

    /**
     * Current locale(en,en_US etc.)
     * @var string
     */
    protected ?string $locale = null;
    /**
     * Current folder in module context
     * @var string
     */
    protected string $localeBase = 'messages';

    protected ?LocaleIdentityInterface $localeIdentity = null;

    protected array $messages = [];

    protected ?FileResolver $fileResolver = null;

    /**
     * Adds a translation file to the translator.
     *
     * @param string $type The type of the translation file.
     * @param string $filename The filename of the translation file.
     * @param string $textDomain The text domain of the translation file. Default is 'default'.
     * @return self Returns an instance of the Translator class.
     */
    public function addTranslationFile(string $type, string $filename, string $textDomain = 'default'): self {

        if (! isset($this->files[$textDomain])) {
            $this->files[$textDomain] = [];
        }

        $this->files[$textDomain][] = [
            'type'     => $type,
            'filename' => $filename,
        ];

        return $this;
    }

    public function getLoaderDispatcher(): LoaderDispatcher {
        if (! $this->loaderDispatcher instanceof LoaderDispatcher) {
            $this->loaderDispatcher = new LoaderDispatcher();
        }

        return $this->loaderDispatcher;
    }

    /**
     * Get the current locale of the translator.
     *
     * @return string The current locale.
     */
    public function getLocale(): string {
        if ($this->locale === null) {
            $this->locale = $this->getLocaleIdentity()->getName();
        }

        return $this->locale;
    }

    /**
     * Retrieves the base path for the specified locale.
     *
     * @param string|null $locale The locale to retrieve the base path for. If null, the default locale will be used.
     * @return string The base path for the specified locale.
     */
    public function getLocaleDir(?string $locale = null): string {
        if ($locale === null) {
            $locale = $this->getLocale();
        }

        // Check if localeBase is a directory or just a folder name
        if (is_dir($this->localeBase)) {
            $basePath = rtrim($this->localeBase, DIRECTORY_SEPARATOR);
        } else {
            $basePath = __DIR__ . DIRECTORY_SEPARATOR . $this->localeBase;
        }

        // Ensure locale is a valid language identifier
        if (! preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $locale)) {
            throw new ERuntimeError('Invalid locale identifier');
        }

        return "{$basePath}/{$locale}";
    }

    public function getLocaleIdentity(): LocaleIdentityInterface {
        if (!$this->localeIdentity instanceof LocaleIdentityInterface) {
            $this->localeIdentity = new LocaleDetector();
        }

        return $this->localeIdentity;
    }

    public function getResolver(): FileResolver {
        if(! $this->fileResolver instanceof FileResolver) {
            $this->fileResolver = new FileResolver();
        }

        return $this->fileResolver;
    }

    public function setLoaderDispatcher(LoaderDispatcher $instance): self {
        $this->loaderDispatcher = $instance;

        return $this;
    }

    /**
     * Sets the locale for the translator.
     *
     * @param string $locale The locale to set.
     * @return void
     */
    public function setLocale(string $locale): void {
        $this->locale = $locale;
    }

    /**
     * Sets the locale identity for translation.
     *
     * @param mixed $instance The locale identity to set.
     * @return void
     */
    public function setLocaleIdentity(mixed $instance): void {
        if (! is_object($instance)) {
            $instance = Hydrator::createInstance($instance);
        }

        if (! $instance instanceof LocaleIdentityInterface) {
            throw new ERuntimeError(
                sprintf(
                    'Expected `LocaleIdentityInterface`, got type "%s" instead',
                    is_object($instance) ? get_class($instance) : gettype($instance)
                )
            );
        }

        $this->localeIdentity = $instance;
    }

    public function setResolver(FileResolver $instance) {
        $this->fileResolver = $instance;
    }

    /**
     * Translates a message to the specified text domain and locale.
     *
     * @param string      $message    The message to be translated.
     * @param string      $textDomain The text domain to translate the message in. Default is 'default'.
     * @param string|null $locale     The locale to translate the message to. If null, the default locale will be used.
     *
     * @return string The translated message.
     */
    public function translate(string $message, string $textDomain = 'default', ?string $locale = null) {
        $locale = $locale ??= $this->getLocale();
        if (($translation = $this->getTranslation($message, $textDomain, $locale)) !== null) {
            return $translation;
        }

        return $message;
    }

    /**
     * Retrieves the translation for a given message, text domain, and locale.
     *
     * @param string $message The message to be translated.
     * @param string $textDomain The text domain for the translation.
     * @param string $locale The locale for the translation.
     * @return mixed The translated message, or null if no translation is found.
     */
    protected function getTranslation(string $message, string $textDomain, string $locale): mixed {
        if (empty($message)) {
            return null;
        }

        // check textDomain
        if (! isset($this->messages[$textDomain][$locale])) {
            if (! $this->loadTranslation($textDomain, $locale)) {
                return null;
            }
        }

        // check & return message
        if (isset($this->messages[$textDomain][$locale][$message])) {
            return $this->messages[$textDomain][$locale][$message];
        }

        return null;
    }

    /**
     * Loads the translation for a specific text domain and locale.
     *
     * @param string $textDomain The text domain to load the translation for.
     * @param string $locale The locale to load the translation in.
     * @return bool Returns true if the translation was successfully loaded, false otherwise.
     */
    protected function loadTranslation(string $textDomain, string $locale): bool {
        $result = false;

        if (! isset($this->messages[$textDomain])) {
            $this->messages[$textDomain] = [];
        }

        if (isset($this->files[$textDomain])) {
            foreach ($this->files[$textDomain] as $file) {
                if (! ($loader = $this->getLoaderDispatcher()->getService($file['type'])) instanceof TranslationLoaderAbstract) {
                    throw new ERuntimeError('Loader is not a translation file loader');
                }

                if ($filename = $this->getResolver()->addPath($this->getLocaleDir($locale))->resolve(basename($file['filename']))) {
                    $messages = $loader->load($filename);
                    if (isset($this->messages[$textDomain][$locale])) {
                        $this->messages[$textDomain][$locale]->exchangeArray(
                            array_replace(
                                $this->messages[$textDomain][$locale]->getArrayCopy(),
                                $messages->getArrayCopy()
                            )
                        );
                    } else {
                        $this->messages[$textDomain][$locale] = $messages;
                    }

                    $result = true;
                }
            }

            reset($this->files[$textDomain]);
        }

        return $result;
    }

    /**
     * Sets the files for translation.
     *
     * @param array $files The array of files to set.
     * @return self The updated Translator instance.
     */
    protected function setFiles(array $files): self {
        foreach ($files as $file) {
            if (isset($file['type'], $file['filename'])) {
                $this->addTranslationFile($file['type'], $file['filename'], $file['textDomain'] ?? 'default');
            }
        }

        return $this;
    }

    /**
     * Get the value of messages
     */ 
    public function getMessages():array
    {
        return $this->messages;
    }
}

/* End of file Translator.php */
