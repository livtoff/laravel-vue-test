<?php

/**
 * Project Setup Script
 *
 * This script performs the following:
 * - Copies .env.example to .env
 * - Updates APP_NAME in .env with a smart default based on folder name
 * - Updates APP_URL and MAIL_FROM_ADDRESS in .env with smart defaults
 * - Updates DB_DATABASE in .env with a smart default based on folder name
 * - Runs php artisan key:generate
 * - Runs composer install
 * - Runs npm install
 * - Optionally deletes itself after completion
 */

// Define the setup steps in order
$setupSteps = [
    'copyEnvFile',
    'updateAppName',
    'updateAppUrl',
    'updateDbName',
    'generateAppKey',
    'installComposerDependencies',
    'installNpmDependencies',
    'setupWhiskey',
    'runMigrations',
    'secureHerd',
    'askDeleteScript',
    'openInBrowser',
    'startNpmDev',
];

// Helper function to get the root folder name
function getRootFolderName()
{
    return basename(getcwd());
}

// Helper function to format folder name for default app name
function formatAppName($folderName)
{
    // Replace dashes and underscores with spaces
    $name = str_replace(['-', '_'], ' ', $folderName);

    // Capitalize words
    return ucwords($name);
}

// Main function to run the setup
function runSetup($steps)
{
    echo "🚀 Starting project setup...\n\n";

    $envContent = '';
    $updated = false;

    foreach ($steps as $step) {
        [$envContent, $updated] = $step($envContent, $updated);
    }

    echo "\n🎉 Setup completed successfully! Your project is ready to go.\n";
}

// Copy .env.example to .env
function copyEnvFile($envContent, $updated)
{
    // Check if .env.example exists
    if (! file_exists('.env.example')) {
        echo "❌ Error: .env.example file not found. Make sure you're running this script from the project root.\n";
        exit(1);
    }

    // Copy .env.example to .env if it doesn't already exist
    if (! file_exists('.env')) {
        echo "📄 Creating .env file from .env.example...\n";
        if (copy('.env.example', '.env')) {
            echo "✅ .env file created successfully.\n\n";
        } else {
            echo "❌ Error: Failed to create .env file.\n";
            exit(1);
        }
    } else {
        echo "📝 .env file already exists. Using existing file.\n\n";
    }

    // Read the .env file
    $envContent = file_get_contents('.env');

    return [$envContent, $updated];
}

// Update APP_NAME in .env with smart default
function updateAppName($envContent, $updated)
{
    // Get default app name from folder name
    $folderName = getRootFolderName();
    $defaultAppName = formatAppName($folderName);

    // Ask for project name with default
    echo "📝 Enter project name [default: {$defaultAppName}]: ";
    $projectName = trim(fgets(STDIN));

    // Use default if empty
    if (empty($projectName)) {
        $projectName = $defaultAppName;
    }

    // Make sure the project name is quoted for proper handling of spaces
    $projectName = '"'.str_replace('"', '\"', $projectName).'"';

    // Update APP_NAME in .env
    $pattern = '/APP_NAME=.*/';
    $replacement = "APP_NAME={$projectName}";
    $envContent = preg_replace($pattern, $replacement, $envContent, -1, $count);

    if ($count > 0) {
        echo "✅ APP_NAME updated to {$projectName}.\n\n";
        $updated = true;
    } else {
        echo "❌ Warning: APP_NAME not found in .env file.\n\n";
    }

    return [$envContent, $updated];
}

// Update APP_URL and MAIL_FROM_ADDRESS in .env with smart default
function updateAppUrl($envContent, $updated)
{
    // Get default URL from folder name
    $folderName = getRootFolderName();
    $defaultUrl = "https://{$folderName}.test";

    // Ask for URL with default
    echo "🌐 Enter application URL [default: {$defaultUrl}]: ";
    $appUrl = trim(fgets(STDIN));

    // Use default if empty
    if (empty($appUrl)) {
        $appUrl = $defaultUrl;
    }

    // Update APP_URL in .env
    $pattern = '/APP_URL=.*/';
    $replacement = "APP_URL={$appUrl}";
    $envContent = preg_replace($pattern, $replacement, $envContent, -1, $count);

    if ($count > 0) {
        echo "✅ APP_URL updated to {$appUrl}.\n";
        $updated = true;
    } else {
        echo "❌ Warning: APP_URL not found in .env file.\n";
    }

    // Extract domain from URL for email
    $domain = parse_url($appUrl, PHP_URL_HOST);
    if ($domain) {
        // Update MAIL_FROM_ADDRESS in .env
        $pattern = '/MAIL_FROM_ADDRESS=.*/';
        $replacement = "MAIL_FROM_ADDRESS=app@{$domain}";
        $envContent = preg_replace($pattern, $replacement, $envContent, -1, $count);

        if ($count > 0) {
            echo "✅ MAIL_FROM_ADDRESS updated to app@{$domain}.\n\n";
            $updated = true;
        } else {
            echo "❌ Warning: MAIL_FROM_ADDRESS not found in .env file.\n\n";
        }
    }

    return [$envContent, $updated];
}

// Update DB_DATABASE in .env with smart default
function updateDbName($envContent, $updated)
{
    // Get default database name from folder name
    $folderName = getRootFolderName();

    // Ask for database name with default
    echo "🗄️ Enter database name [default: {$folderName}]: ";
    $dbName = trim(fgets(STDIN));

    // Use default if empty
    if (empty($dbName)) {
        $dbName = $folderName;
    }

    // Update DB_DATABASE in .env
    $pattern = '/DB_DATABASE=.*/';
    $replacement = "DB_DATABASE={$dbName}";
    $envContent = preg_replace($pattern, $replacement, $envContent, -1, $count);

    if ($count > 0) {
        echo "✅ DB_DATABASE updated to {$dbName}.\n\n";
        $updated = true;
    } else {
        echo "❌ Warning: DB_DATABASE not found in .env file.\n\n";
    }

    // Save changes to .env file if updates were made
    if ($updated) {
        if (file_put_contents('.env', $envContent)) {
            echo "✅ All changes saved to .env file.\n\n";
        } else {
            echo "❌ Error: Failed to save changes to .env file.\n\n";
            exit(1);
        }
    } else {
        echo "ℹ️ No changes were made to the .env file.\n\n";
    }

    return [$envContent, $updated];
}

// Run artisan key:generate
function generateAppKey($envContent, $updated)
{
    echo "🔑 Generating application key...\n";
    $output = [];
    $returnVar = 0;
    exec('php artisan key:generate', $output, $returnVar);

    if ($returnVar === 0) {
        echo "✅ Application key generated successfully.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    } else {
        echo "❌ Failed to generate application key.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    }

    return [$envContent, $updated];
}

// Run composer install
function installComposerDependencies($envContent, $updated)
{
    echo "📦 Installing PHP dependencies with Composer...\n";
    $output = [];
    $returnVar = 0;
    exec('composer install', $output, $returnVar);

    if ($returnVar === 0) {
        echo "✅ Composer dependencies installed successfully.\n\n";
    } else {
        echo "❌ Failed to install Composer dependencies.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    }

    return [$envContent, $updated];
}

// Run npm install
function installNpmDependencies($envContent, $updated)
{
    echo "📦 Installing Node.js dependencies with npm...\n";
    $output = [];
    $returnVar = 0;
    exec('npm install', $output, $returnVar);

    if ($returnVar === 0) {
        echo "✅ npm dependencies installed successfully.\n\n";
    } else {
        echo "❌ Failed to install npm dependencies.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    }

    return [$envContent, $updated];
}

// Run artisan key:generate
function setupWhiskey($envContent, $updated)
{
    echo "🥃 Setting up Whiskey...\n";
    $output = [];
    $returnVar = 0;
    exec('./vendor/bin/whisky install -n', $output, $returnVar);

    echo "returnVar: $returnVar\n";

    if ($returnVar === 0) {
        echo "✅ Whiskey setup completed successfully.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    } else {
        echo "❌ Failed to setup Whiskey.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    }

    return [$envContent, $updated];
}

// Run artisan migrate
function runMigrations($envContent, $updated)
{
    echo "🗃️ Running database migrations...\n";
    $output = [];
    $returnVar = 0;
    exec('php artisan migrate --force', $output, $returnVar);

    if ($returnVar === 0) {
        echo "✅ Database migrations completed successfully.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    } else {
        echo "❌ Failed to run database migrations.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    }

    return [$envContent, $updated];
}

// Secure Herd
function secureHerd($envContent, $updated)
{
    echo "🔒 Securing site with Herd...\n";
    $output = [];
    $returnVar = 0;
    exec('herd secure', $output, $returnVar);

    if ($returnVar === 0) {
        echo "✅ Site secured with Herd successfully.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    } else {
        echo "❌ Failed to secure site with Herd.\n";
        foreach ($output as $line) {
            echo "   $line\n";
        }
        echo "\n";
    }

    return [$envContent, $updated];
}

// Open in browser
function openInBrowser($envContent, $updated)
{
    // Extract APP_URL from .env content
    if (preg_match('/APP_URL=(.*)/', $envContent, $matches)) {
        $appUrl = trim($matches[1]);

        // Create a background process to open the browser after a delay
        echo "🌐 Setting up delayed browser opening (will open in 3 seconds)...\n";

        // Create a temporary script file to open the browser after delay
        $tempScript = sys_get_temp_dir().'/open_browser_'.uniqid().'.php';
        $scriptContent = <<<EOT
<?php
// Wait for 3 seconds
sleep(3);
// Open the browser
exec('open -n {$appUrl}');
// Delete this temporary script
unlink(__FILE__);
EOT;

        file_put_contents($tempScript, $scriptContent);

        // Execute the temporary script in the background
        exec("php {$tempScript} > /dev/null 2>&1 &");

        echo "✅ Browser will open automatically in a few seconds.\n\n";
    } else {
        echo "❌ Could not find APP_URL in .env file to open in browser.\n\n";
    }

    return [$envContent, $updated];
}

// Ask if the script should delete itself
function askDeleteScript($envContent, $updated)
{
    echo '🗑️ Would you like to delete this setup script? (y/n) [default: yes]: ';

    $deleteScript = strtolower(trim(fgets(STDIN)));

    if (empty($deleteScript)) {
        $deleteScript = 'y';
    }

    if ($deleteScript === 'y' || $deleteScript === 'yes') {
        // Get the current script file path
        $scriptPath = __FILE__;

        echo "🗑️ Deleting setup script...\n";
        if (unlink($scriptPath)) {
            echo "✅ Setup script deleted successfully.\n";
        } else {
            echo "❌ Failed to delete setup script.\n";
        }
    }

    return [$envContent, $updated];
}

// Start npm run dev
function startNpmDev($envContent, $updated)
{
    echo "🚀 Starting npm development server...\n";
    echo "✅ Running 'npm run dev' in the terminal...\n";

    // Pass control to npm run dev in the terminal
    passthru('npm run dev');

    // Note: The script will continue only after npm run dev is terminated
    echo "✅ npm development server has been stopped.\n\n";

    return [$envContent, $updated];
}

// Run the setup with the defined steps
runSetup($setupSteps);
