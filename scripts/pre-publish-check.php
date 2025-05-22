<?php

/**
 * Pre-publication verification script
 * Run this script to verify your package is ready for publication
 */
class PrePublishChecker
{
    private array $errors = [];

    private array $warnings = [];

    private string $packagePath;

    public function __construct(string $packagePath = __DIR__.'/..')
    {
        $this->packagePath = realpath($packagePath);
    }

    public function runAllChecks(): bool
    {
        echo "🔍 Running pre-publication checks for Binance API package...\n\n";

        $this->checkRequiredFiles();
        $this->checkComposerJson();
        $this->checkCodeQuality();
        $this->checkDocumentation();
        $this->checkSecurity();
        $this->checkGitConfiguration();

        $this->displayResults();

        return empty($this->errors);
    }

    private function checkRequiredFiles(): void
    {
        echo "📁 Checking required files...\n";

        $requiredFiles = [
            'composer.json' => 'Package configuration',
            'README.md' => 'Main documentation',
            'LICENSE' => 'License file',
            'CHANGELOG.md' => 'Version history',
            'CONTRIBUTING.md' => 'Contribution guidelines',
            '.gitignore' => 'Git ignore rules',
            'src/BinanceApiServiceProvider.php' => 'Service provider',
            'config/binance-api.php' => 'Configuration file',
        ];

        foreach ($requiredFiles as $file => $description) {
            if (! file_exists($this->packagePath.'/'.$file)) {
                $this->errors[] = "Missing required file: {$file} ({$description})";
            } else {
                echo "  ✅ {$file}\n";
            }
        }

        echo "\n";
    }

    private function checkComposerJson(): void
    {
        echo "📦 Checking composer.json...\n";

        $composerFile = $this->packagePath.'/composer.json';
        if (! file_exists($composerFile)) {
            $this->errors[] = 'composer.json file is missing';

            return;
        }

        $composer = json_decode(file_get_contents($composerFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->errors[] = 'composer.json is not valid JSON';

            return;
        }

        // Check required fields
        $requiredFields = [
            'name' => 'Package name',
            'description' => 'Package description',
            'type' => 'Package type',
            'license' => 'License',
            'authors' => 'Author information',
            'require' => 'Dependencies',
            'autoload' => 'Autoloading configuration',
        ];

        foreach ($requiredFields as $field => $description) {
            if (! isset($composer[$field])) {
                $this->errors[] = "Missing required field in composer.json: {$field} ({$description})";
            } else {
                echo "  ✅ {$field}\n";
            }
        }

        // Check Laravel auto-discovery
        if (! isset($composer['extra']['laravel']['providers'])) {
            $this->warnings[] = 'Laravel auto-discovery providers not configured';
        } else {
            echo "  ✅ Laravel auto-discovery configured\n";
        }

        echo "\n";
    }

    private function checkCodeQuality(): void
    {
        echo "🔧 Checking code quality...\n";

        // Check if main classes exist
        $coreClasses = [
            'src/Services/BinanceApiService.php',
            'src/Services/AccountService.php',
            'src/Services/OrderService.php',
            'src/Services/MarketService.php',
            'src/Exceptions/BinanceApiException.php',
            'src/Facades/BinanceApi.php',
        ];

        foreach ($coreClasses as $class) {
            if (file_exists($this->packagePath.'/'.$class)) {
                echo "  ✅ {$class}\n";
            } else {
                $this->errors[] = "Missing core class: {$class}";
            }
        }

        // Check for vendor directory (shouldn't be committed)
        if (is_dir($this->packagePath.'/vendor')) {
            $this->warnings[] = "vendor/ directory exists - make sure it's in .gitignore";
        }

        echo "\n";
    }

    private function checkDocumentation(): void
    {
        echo "📚 Checking documentation...\n";

        // Check README content
        $readmePath = $this->packagePath.'/README.md';
        if (file_exists($readmePath)) {
            $readme = file_get_contents($readmePath);

            $requiredSections = [
                'Installation' => '## Installation',
                'Usage examples' => '```php',
                'Features' => '## Features',
            ];

            foreach ($requiredSections as $name => $pattern) {
                if (strpos($readme, $pattern) !== false) {
                    echo "  ✅ README contains {$name}\n";
                } else {
                    $this->warnings[] = "README.md missing {$name} section";
                }
            }
        }

        // Check if INSTALLATION.md exists
        if (file_exists($this->packagePath.'/INSTALLATION.md')) {
            echo "  ✅ Installation guide exists\n";
        } else {
            $this->warnings[] = 'Consider adding detailed INSTALLATION.md';
        }

        echo "\n";
    }

    private function checkSecurity(): void
    {
        echo "🔒 Checking security...\n";

        // Check for sensitive files that shouldn't be committed
        $sensitiveFiles = [
            '.env',
            'auth.json',
            'config/secrets.php',
        ];

        $foundSensitive = false;
        foreach ($sensitiveFiles as $file) {
            if (file_exists($this->packagePath.'/'.$file)) {
                $this->errors[] = "Sensitive file found: {$file} - should not be committed";
                $foundSensitive = true;
            }
        }

        if (! $foundSensitive) {
            echo "  ✅ No sensitive files found\n";
        }

        // Check .gitignore
        $gitignorePath = $this->packagePath.'/.gitignore';
        if (file_exists($gitignorePath)) {
            $gitignore = file_get_contents($gitignorePath);
            $importantIgnores = ['.env', 'vendor/', '.idea/'];

            foreach ($importantIgnores as $ignore) {
                if (strpos($gitignore, $ignore) !== false) {
                    echo "  ✅ .gitignore includes {$ignore}\n";
                } else {
                    $this->warnings[] = ".gitignore should include {$ignore}";
                }
            }
        }

        echo "\n";
    }

    private function checkGitConfiguration(): void
    {
        echo "🌐 Checking Git configuration...\n";

        // Check if it's a git repository
        if (! is_dir($this->packagePath.'/.git')) {
            $this->warnings[] = "Not a Git repository - initialize with 'git init'";

            return;
        }

        echo "  ✅ Git repository initialized\n";

        // Check for GitHub Actions
        if (is_dir($this->packagePath.'/.github/workflows')) {
            echo "  ✅ GitHub Actions configured\n";
        } else {
            $this->warnings[] = 'Consider adding GitHub Actions for CI/CD';
        }

        echo "\n";
    }

    private function displayResults(): void
    {
        echo "📊 Pre-publication Check Results\n";
        echo str_repeat('=', 50)."\n\n";

        if (empty($this->errors) && empty($this->warnings)) {
            echo "🎉 Congratulations! Your package is ready for publication!\n";
            echo "\n✅ All checks passed successfully.\n";
            echo "\n🚀 Next steps:\n";
            echo "   1. Commit and push to GitHub\n";
            echo "   2. Create a release tag (e.g., v1.0.0)\n";
            echo "   3. Submit to Packagist\n";
            echo "   4. Promote in the community\n";

            return;
        }

        if (! empty($this->errors)) {
            echo "❌ ERRORS (must fix before publishing):\n";
            foreach ($this->errors as $error) {
                echo "   • {$error}\n";
            }
            echo "\n";
        }

        if (! empty($this->warnings)) {
            echo "⚠️  WARNINGS (recommended to fix):\n";
            foreach ($this->warnings as $warning) {
                echo "   • {$warning}\n";
            }
            echo "\n";
        }

        if (! empty($this->errors)) {
            echo "🔧 Please fix the errors above before publishing.\n";
        } else {
            echo "✅ No critical errors found. You can publish, but consider addressing the warnings.\n";
        }
    }
}

// Run the check
$checker = new PrePublishChecker;
$success = $checker->runAllChecks();

exit($success ? 0 : 1);
