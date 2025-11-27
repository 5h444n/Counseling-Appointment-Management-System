#!/usr/bin/env node
// Fun terminal fireworks display for Node.js
// Save as test.js and run: node test.js

const cols = Math.min(process.stdout.columns || 80, 100);
const rows = Math.min(Math.max(process.stdout.rows || 24, 20), 40);
const TICK = 60;
const DURATION = 12000; // ms

const colors = [
    '\x1b[31m', // red
    '\x1b[33m', // yellow
    '\x1b[32m', // green
    '\x1b[36m', // cyan
    '\x1b[34m', // blue
    '\x1b[35m', // magenta
];
const reset = '\x1b[0m';
const hideCursor = '\x1b[?25l';
const showCursor = '\x1b[?25h';

function rand(a, b) { return Math.random() * (b - a) + a; }
function randi(a, b) { return Math.floor(rand(a, b + 1)); }

class Particle {
    constructor(x, y, vx, vy, life, ch, color) {
        this.x = x; this.y = y; this.vx = vx; this.vy = vy;
        this.life = life; this.ttl = life;
        this.ch = ch; this.color = color;
    }
    update(dt) {
        this.x += this.vx * dt;
        this.y += this.vy * dt;
        this.vy += 9.8 * dt * 0.5; // gravity
        this.ttl -= dt;
        return this.ttl > 0 && this.y < rows + 2;
    }
    intensity() { return Math.max(0, this.ttl / this.life); }
}

let particles = [];
let last = Date.now();

function spawnFirework() {
    const cx = randi(6, cols - 6);
    const cy = randi(4, Math.max(6, Math.floor(rows / 2)));
    const count = randi(20, 40);
    const color = colors[randi(0, colors.length - 1)];
    const chars = ['✦', '✶', '✨', '*', '•', 'o'];
    for (let i = 0; i < count; i++) {
        const angle = rand(0, Math.PI * 2);
        const speed = rand(3, 9);
        const vx = Math.cos(angle) * speed;
        const vy = Math.sin(angle) * speed * -1;
        const life = rand(0.8, 2.2);
        const ch = chars[randi(0, chars.length - 1)];
        particles.push(new Particle(cx, cy, vx, vy, life, ch, color));
    }
}

function render() {
    // create grid
    const grid = Array.from({length: rows}, () => Array(cols).fill(' '));
    // draw ground
    const groundRow = rows - 2;
    for (let c = 0; c < cols; c++) grid[groundRow][c] = '_';

    // paint particles
    for (const p of particles) {
        const x = Math.round(p.x);
        const y = Math.round(p.y);
        if (x >= 0 && x < cols && y >= 0 && y < rows) {
            const char = p.ch;
            grid[y][x] = `${p.color}${char}${reset}`;
        }
    }

    // draw to terminal
    process.stdout.write('\x1b[H'); // cursor home
    let out = '';
    for (let r = 0; r < rows; r++) {
        out += grid[r].join('') + '\n';
    }
    out += '   Enjoy the terminal fireworks! Press Ctrl+C to exit.\n';
    process.stdout.write(out);
}

function step() {
    const now = Date.now();
    const dt = (now - last) / 1000;
    last = now;

    // update particles
    particles = particles.filter(p => p.update(dt));

    // occasionally spawn new fireworks
    if (Math.random() < 0.25) spawnFirework();

    render();
}

// initialize
process.stdout.write('\x1b[2J'); // clear screen
process.stdout.write(hideCursor);
process.on('SIGINT', cleanup);
process.on('exit', cleanup);

let interval = setInterval(step, TICK);
setTimeout(() => { clearInterval(interval); finish(); }, DURATION);

// final flourish
spawnFirework(); spawnFirework();

function finish() {
    // let remaining particles animate out
    const endInterval = setInterval(() => {
        if (particles.length === 0) {
            clearInterval(endInterval);
            cleanup();
            process.exit(0);
        } else {
            step();
        }
    }, TICK);
}

function cleanup() {
    process.stdout.write(showCursor);
    process.stdout.write(reset);
    process.stdout.write('\n');
}