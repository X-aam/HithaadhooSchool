import { spawn, spawnSync } from 'node:child_process';

const minimumPhpVersion = '8.4.1';
const viteArgs = process.argv.slice(2);
const mode = viteArgs[0] === 'build' ? 'build' : 'dev';

function compareVersions(left, right) {
    const leftParts = left.split('.').map(Number);
    const rightParts = right.split('.').map(Number);
    const length = Math.max(leftParts.length, rightParts.length);

    for (let index = 0; index < length; index += 1) {
        const leftPart = leftParts[index] ?? 0;
        const rightPart = rightParts[index] ?? 0;

        if (leftPart > rightPart) {
            return 1;
        }

        if (leftPart < rightPart) {
            return -1;
        }
    }

    return 0;
}

function detectPhpVersion() {
    const result = spawnSync('php', ['-r', 'echo PHP_VERSION;'], {
        encoding: 'utf8',
    });

    if (result.status !== 0) {
        return null;
    }

    const version = result.stdout.trim();

    return version || null;
}

const phpVersion = detectPhpVersion();
const env = {
    ...process.env,
};

if (!phpVersion || compareVersions(phpVersion, minimumPhpVersion) < 0) {
    env.SKIP_WAYFINDER = '1';

    const versionLabel = phpVersion ?? 'not found';
    console.log(`Skipping Wayfinder generation for ${mode}: PHP ${versionLabel} does not satisfy >= ${minimumPhpVersion}.`);
}

const vite = spawn(process.execPath, ['./node_modules/vite/bin/vite.js', ...viteArgs], {
    stdio: 'inherit',
    env,
    shell: false,
});

vite.on('exit', (code, signal) => {
    if (signal) {
        process.kill(process.pid, signal);

        return;
    }

    process.exit(code ?? 0);
});
