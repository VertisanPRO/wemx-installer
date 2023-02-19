<?php

namespace Wemx\Installer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Matriphe\ISO639\ISO639;
use Wemx\Installer\FileEditor;

class LangCommand extends Command
{

    protected $signature = 'billing:lang {--action= : import/generate} {--locale= : en}';
    protected $description = 'Install Billing localization for Pterodactyl';

    private const SEP = DIRECTORY_SEPARATOR;
    private $locale, $fallback_locale, $lang_path, $default_file, $iosLangs;
    private $args = [];

    private const ACTIONS = [
        'import' => 'Import localization (Imports additional Billing translations into all existing localizations, if any)',
        'generate' => 'Generate new localization (This command will generate files of the selected localization of Billing and Pterodactyl)',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->locale = config('app.locale') ? config('app.locale') : 'en';
        $this->fallback_locale = config('app.fallback_locale') ? config('app.fallback_locale') : 'en';
        $this->lang_path = resource_path('lang');
        $this->default_file = $this->lang_path . self::SEP . 'default.php';
        $this->createDir([
            $this->lang_path . self::SEP . $this->locale . self::SEP . 'billing',
            $this->lang_path . self::SEP . $this->fallback_locale . self::SEP . 'billing',
        ]);

    }

    public function handle()
    {
        $this->args['ACTIONS'] = $this->option('action') ?? $this->choice(
            'Choose an action',
            self::ACTIONS
        );

        switch ($this->args['ACTIONS']) {
            case 'import':
                $this->import();
                break;
            case 'generate':
                $this->generate();
                break;
            default:
                $this->import();
                break;
        }

    }

    private function import()
    {
        foreach ($this->getExistLocalization() as $key => $value) {
            $file = $value . self::SEP . 'billing' . self::SEP . 'client.php';
            if (!file_exists($file)) {
                $content = file_get_contents("https://raw.githubusercontent.com/Mubeen142/billing-docs/main/lang/{$key}/billing/client.php");
                if (strlen($content) > 100) {
                    file_put_contents($file, $content);
                    $this->info("The localization {$this->languages()[$key]} was found on GITHUB and added");
                    continue;
                }
                File::copy($this->default_file, $file);
                $this->info("The localization {$this->languages()[$key]} was generated with a standard translation");
            } else {
              $this->info("Update localization {$this->languages()[$key]}");
              $this->updateLocalization($file);
            }
        }
        $this->info("Import done");
    }

    private function generate()
    {
        $code = $this->option('locale') ?? $this->choice(
            'Choose an locale',
            $this->languages()
        );

        $file = $this->lang_path . self::SEP . $code . self::SEP . 'billing' . self::SEP . 'client.php';
        if (!file_exists($file)) {
            $this->copyDirectory($this->lang_path . self::SEP . 'en', $this->lang_path . self::SEP . $code);
            $content = file_get_contents("https://raw.githubusercontent.com/Mubeen142/billing-docs/main/lang/{$code}/billing/client.php");
            if (strlen($content) > 100) {
                file_put_contents($file, $content);
                $this->info("The localization {$this->languages()[$code]} was found on GITHUB and added");
                return;
            }
            File::copy($this->default_file, $file);
            $this->info("The localization {$this->languages()[$code]} was generated with a standard translation");
        } else {
            $this->warn("Canceled {$this->languages()[$code]}!!! Localization already exists");
        }

    }

    private function getExistLocalization()
    {
        $langs_dirs = File::directories($this->lang_path);
        $langs = [];
        foreach ($langs_dirs as $value) {
            $langs[basename($value)] = $value;
        }
        return $langs;
    }

    private function createDir($path)
    {
        if (is_array($path)) {
            foreach ($path as $dir) {
                if (!file_exists($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
            }
            return;
        }
        if (!file_exists($path)) {
            File::makeDirectory($path, 0755, true);

        }
    }

    private function copyDirectory($source_path, $destination_path)
    {
        if (!File::isDirectory($destination_path)) {
            File::makeDirectory($destination_path, 0755, true);
        }
        File::copyDirectory($source_path, $destination_path);

    }

    private function languages()
    {
        $this->iosLangs = new ISO639;
        $codes = [];
        foreach ($this->iosLangs->allLanguages() as $key => $value) {
            $codes[$value[0]] = $value[4];
        }
        return $codes;
    }

    private function updateLocalization($edit_locale_path)
    {
        $default_locale = include $this->default_file;
        $edit_locale = include $edit_locale_path;
        foreach ($default_locale as $key => $value) {
            if (isset($edit_locale[$key])) {
                continue;
            }
            FileEditor::appendBefore($edit_locale_path, '];', "    '{$key}' => " . json_encode($value) . ",");
        }
        return;
    }

}
