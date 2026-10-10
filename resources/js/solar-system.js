/**
 * solar-system.js — Signature 3D SOLAR SYSTEM / COSMIC TECHNOLOGY environment (§3–§5).
 *
 * Fixed full-viewport background canvas: central glowing sun, orbiting planets
 * with rings, orbital path lines, starfield, particles, distant galaxy sprites,
 * atmospheric glow, nebula wash, satellite/data nodes + connection lines.
 *
 * Guarantees:
 * - Background only: canvas is fixed, z-index 0, pointer-events none (CSS).
 * - Perf: device capability detection, adaptive pixel ratio, reduced particle
 *   counts on mobile, throttled when tab hidden / scrolled out, lazy via
 *   IntersectionObserver, honors prefers-reduced-motion (static frame only).
 * - Fallback: `.solar-fallback-bg` div shown when WebGL unavailable.
 */
import * as THREE from 'three';

class SolarSystem {
    constructor(canvasId = 'solar-system-canvas') {
        this.canvasId = canvasId;
        this.canvas = null;
        this.renderer = null;
        this.scene = null;
        this.camera = null;
        this.frameId = null;
        this.clock = new THREE.Clock();
        this.mouse = { x: 0, y: 0, tx: 0, ty: 0 };
        this.planets = [];
        this.satellites = [];
        this.isReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        this.isDestroyed = false;
        this.isVisible = true;
        this.scrollY = 0;
        this.init();
    }

    isMobile() {
        return window.innerWidth < 768 || /Mobi|Android/i.test(navigator.userAgent);
    }

    isWebGLAvailable() {
        try {
            const c = document.createElement('canvas');
            return !!(window.WebGLRenderingContext && (c.getContext('webgl') || c.getContext('experimental-webgl')));
        } catch (e) { return false; }
    }

    showFallback() {
        const fb = document.querySelector('.solar-fallback-bg');
        if (fb) fb.style.display = 'block';
        if (this.canvas) this.canvas.style.display = 'none';
    }

    init() {
        this.canvas = document.getElementById(this.canvasId);
        if (!this.canvas) return;
        if (this.canvas.dataset.ssInit === '1') return;
        this.canvas.dataset.ssInit = '1';

        if (!this.isWebGLAvailable() || this.isReduced) { this.showFallback(); return; }
        const fb = document.querySelector('.solar-fallback-bg');
        if (fb) fb.style.display = 'none';

        try {
            const mobile = this.isMobile();
            this.renderer = new THREE.WebGLRenderer({ canvas: this.canvas, alpha: true, antialias: !mobile, powerPreference: 'low-power' });
            // Adaptive pixel ratio (§5): cap DPR, lower on mobile
            this.renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, mobile ? 1.25 : 1.75));
            this.renderer.setSize(window.innerWidth, window.innerHeight);
            this.renderer.setClearColor(0x000000, 0);
        } catch (e) { this.showFallback(); return; }

        this.scene = new THREE.Scene();
        this.scene.fog = new THREE.FogExp2(0x030308, 0.016);
        this.camera = new THREE.PerspectiveCamera(55, window.innerWidth / window.innerHeight, 0.1, 500);
        this.camera.position.set(0, 3.4, 16);

        this.scene.add(new THREE.AmbientLight(0x334155, 0.9));
        const sunLight = new THREE.PointLight(0xfff7e0, 2.2, 90);
        sunLight.position.set(0, 0, 0);
        this.scene.add(sunLight);

        this.buildStars();
        this.buildNebula();
        this.buildSun();
        this.buildPlanets();
        this.buildSatellites();
        this.buildDust();
        this.bindEvents();
        // Render one frame immediately so background is never blank, then loop
        this.tick(true);
        this.animate();
    }

    radialTexture(inner, outer) {
        const c = document.createElement('canvas');
        c.width = c.height = 128;
        const ctx = c.getContext('2d');
        const g = ctx.createRadialGradient(64, 64, 2, 64, 64, 64);
        g.addColorStop(0, inner);
        g.addColorStop(1, outer);
        ctx.fillStyle = g;
        ctx.fillRect(0, 0, 128, 128);
        const t = new THREE.CanvasTexture(c);
        t.needsUpdate = true;
        return t;
    }

    buildStars() {
        const mobile = this.isMobile();
        const count = mobile ? 700 : 2200;
        const pos = new Float32Array(count * 3);
        const col = new Float32Array(count * 3);
        const palette = [new THREE.Color(0xffffff), new THREE.Color(0xcbd5e1), new THREE.Color(0x93c5fd), new THREE.Color(0xe2e8f0)];
        for (let i = 0; i < count; i++) {
            const r = 45 + Math.random() * 110;
            const th = Math.random() * Math.PI * 2;
            const ph = Math.acos(Math.random() * 2 - 1);
            pos[i * 3] = r * Math.sin(ph) * Math.cos(th);
            pos[i * 3 + 1] = r * Math.sin(ph) * Math.sin(th);
            pos[i * 3 + 2] = r * Math.cos(ph);
            const c = palette[(Math.random() * palette.length) | 0];
            col[i * 3] = c.r; col[i * 3 + 1] = c.g; col[i * 3 + 2] = c.b;
        }
        const g = new THREE.BufferGeometry();
        g.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        g.setAttribute('color', new THREE.BufferAttribute(col, 3));
        const m = new THREE.PointsMaterial({
            size: 0.55, map: this.radialTexture('rgba(255,255,255,1)', 'rgba(255,255,255,0)'),
            vertexColors: true, transparent: true, opacity: 0.85,
            blending: THREE.AdditiveBlending, depthWrite: false, sizeAttenuation: true,
        });
        this.stars = new THREE.Points(g, m);
        this.scene.add(this.stars);
    }

    buildNebula() {
        this.nebula = new THREE.Group();
        const tex = this.radialTexture('rgba(255,255,255,0.85)', 'rgba(255,255,255,0)');
        const cfgs = [
            { p: [14, 5, -30], s: 34, c: 0x475569, o: 0.16 },
            { p: [-16, -6, -34], s: 40, c: 0x334155, o: 0.14 },
            { p: [0, 10, -42], s: 48, c: 0x1e293b, o: 0.16 },
        ];
        cfgs.forEach((cf) => {
            const sp = new THREE.Sprite(new THREE.SpriteMaterial({ map: tex, color: cf.c, transparent: true, opacity: cf.o, blending: THREE.AdditiveBlending, depthWrite: false }));
            sp.position.set(...cf.p);
            sp.scale.set(cf.s, cf.s * 0.75, 1);
            this.nebula.add(sp);
        });
        this.scene.add(this.nebula);
    }

    buildSun() {
        const geo = new THREE.SphereGeometry(2.1, 48, 48);
        const mat = new THREE.MeshBasicMaterial({ color: 0xfffbeb });
        this.sun = new THREE.Mesh(geo, mat);
        this.sun.position.set(-5.5, 1.2, -6);
        this.scene.add(this.sun);
        const glowTex = this.radialTexture('rgba(255,251,235,0.9)', 'rgba(255,251,235,0)');
        const glow = new THREE.Sprite(new THREE.SpriteMaterial({ map: glowTex, color: 0xfde68a, transparent: true, opacity: 0.55, blending: THREE.AdditiveBlending, depthWrite: false }));
        glow.scale.set(11, 11, 1);
        glow.position.copy(this.sun.position);
        this.scene.add(glow);
        this.sunGlow = glow;
        // Sun corona ring
        const ring = new THREE.Mesh(
            new THREE.RingGeometry(2.6, 2.75, 96),
            new THREE.MeshBasicMaterial({ color: 0xfde68a, transparent: true, opacity: 0.28, side: THREE.DoubleSide, blending: THREE.AdditiveBlending })
        );
        ring.position.copy(this.sun.position);
        this.scene.add(ring);
        this.sunRing = ring;
    }

    buildPlanets() {
        const defs = [
            { r: 0.32, orbit: 4.6, speed: 0.22, color: 0x9ca3af, tilt: 0.2 },
            { r: 0.48, orbit: 6.2, speed: 0.16, color: 0x94a3b8, tilt: 0.35, ring: true },
            { r: 0.62, orbit: 8.1, speed: 0.12, color: 0x60a5fa, tilt: 0.4, atmosphere: true },
            { r: 0.5, orbit: 10.1, speed: 0.09, color: 0xc084fc, tilt: 0.5 },
            { r: 0.85, orbit: 12.4, speed: 0.065, color: 0x38bdf8, tilt: 0.3, ring: true },
            { r: 0.4, orbit: 14.6, speed: 0.05, color: 0xe2e8f0, tilt: 0.6 },
        ];
        const orbitMat = new THREE.LineBasicMaterial({ color: 0x64748b, transparent: true, opacity: 0.28 });
        defs.forEach((d, i) => {
            // Orbit path
            const pts = [];
            for (let a = 0; a <= 128; a++) {
                const t = (a / 128) * Math.PI * 2;
                pts.push(new THREE.Vector3(Math.cos(t) * d.orbit - 1.5, 0, Math.sin(t) * d.orbit * 0.55 - 2));
            }
            const line = new THREE.Line(new THREE.BufferGeometry().setFromPoints(pts), orbitMat);
            this.scene.add(line);
            // Planet
            const mesh = new THREE.Mesh(
                new THREE.SphereGeometry(d.r, 32, 32),
                new THREE.MeshStandardMaterial({ color: d.color, roughness: 0.65, metalness: 0.35, emissive: d.color, emissiveIntensity: 0.08 })
            );
            mesh.rotation.z = d.tilt;
            this.scene.add(mesh);
            if (d.atmosphere) {
                const atm = new THREE.Mesh(
                    new THREE.SphereGeometry(d.r * 1.12, 24, 24),
                    new THREE.MeshBasicMaterial({ color: 0x38bdf8, transparent: true, opacity: 0.22, side: THREE.BackSide, blending: THREE.AdditiveBlending })
                );
                mesh.add(atm);
            }
            if (d.ring) {
                const rg = new THREE.Mesh(
                    new THREE.RingGeometry(d.r * 1.4, d.r * 2.1, 64),
                    new THREE.MeshBasicMaterial({ color: 0xcbd5e1, transparent: true, opacity: 0.3, side: THREE.DoubleSide, blending: THREE.AdditiveBlending })
                );
                rg.rotation.x = Math.PI / 2.4;
                mesh.add(rg);
            }
            this.planets.push({ ...d, mesh, angle: (i / defs.length) * Math.PI * 2 + Math.random() });
        });
    }

    buildSatellites() {
        // Data-node satellites + connection lines back toward the sun
        const nodeGeo = new THREE.SphereGeometry(0.07, 12, 12);
        const lineMat = new THREE.LineBasicMaterial({ color: 0x94a3b8, transparent: true, opacity: 0.3, blending: THREE.AdditiveBlending });
        for (let i = 0; i < 7; i++) {
            const mat = new THREE.MeshBasicMaterial({ color: i % 2 ? 0x38bdf8 : 0xe2e8f0 });
            const node = new THREE.Mesh(nodeGeo, mat);
            const a = Math.random() * Math.PI * 2;
            const rad = 5 + Math.random() * 9;
            node.position.set(Math.cos(a) * rad - 1.5, (Math.random() - 0.5) * 5, Math.sin(a) * rad * 0.55 - 2);
            node.userData = { a, rad, sp: 0.1 + Math.random() * 0.15, y: node.position.y };
            this.scene.add(node);
            const lg = new THREE.BufferGeometry().setFromPoints([node.position, this.sun.position]);
            const line = new THREE.Line(lg, lineMat);
            this.scene.add(line);
            node.userData.line = line;
            this.satellites.push(node);
        }
    }

    buildDust() {
        const mobile = this.isMobile();
        const count = mobile ? 120 : 350;
        const pos = new Float32Array(count * 3);
        for (let i = 0; i < count; i++) {
            pos[i * 3] = (Math.random() - 0.5) * 36;
            pos[i * 3 + 1] = (Math.random() - 0.5) * 18;
            pos[i * 3 + 2] = -2 - Math.random() * 14;
        }
        const g = new THREE.BufferGeometry();
        g.setAttribute('position', new THREE.BufferAttribute(pos, 3));
        this.dust = new THREE.Points(g, new THREE.PointsMaterial({
            size: 0.12, color: 0xcbd5e1, transparent: true, opacity: 0.5,
            blending: THREE.AdditiveBlending, depthWrite: false,
        }));
        this.scene.add(this.dust);
    }

    bindEvents() {
        this.onMouse = (e) => {
            this.mouse.tx = (e.clientX / window.innerWidth - 0.5) * 2;
            this.mouse.ty = (e.clientY / window.innerHeight - 0.5) * 2;
        };
        this.onResize = () => {
            if (!this.renderer) return;
            this.camera.aspect = window.innerWidth / window.innerHeight;
            this.camera.updateProjectionMatrix();
            this.renderer.setSize(window.innerWidth, window.innerHeight);
        };
        this.onScroll = () => {
            this.scrollY = window.scrollY || 0;
        };
        this.onVis = () => {
            this.isVisible = !document.hidden;
            if (this.isVisible && !this.isDestroyed && !this.frameId) this.animate();
        };
        window.addEventListener('mousemove', this.onMouse, { passive: true });
        window.addEventListener('resize', this.onResize, { passive: true });
        window.addEventListener('scroll', this.onScroll, { passive: true });
        document.addEventListener('visibilitychange', this.onVis);
        if ('IntersectionObserver' in window && this.canvas) {
            this.ob = new IntersectionObserver((en) => {
                this.isVisible = en[0].isIntersecting && !document.hidden;
                if (this.isVisible && !this.isDestroyed && !this.frameId) this.animate();
            });
            this.ob.observe(this.canvas);
        }
        // Pause offscreen canvas rendering when footer/hero 3D present is enough —
        // solar canvas is fixed so it stays visible; visibility handler suffices.
    }

    tick(staticFrame = false) {
        const t = this.clock.getElapsedTime();
        // Planets orbit
        this.planets.forEach((p) => {
            p.angle += staticFrame ? 0 : 0.0016 * p.speed * 60 * 0.016;
            p.mesh.position.set(Math.cos(p.angle) * p.orbit - 1.5, Math.sin(p.angle * 0.7) * 0.4, Math.sin(p.angle) * p.orbit * 0.55 - 2);
            p.mesh.rotation.y += staticFrame ? 0 : 0.004;
        });
        // Satellites drift + update links
        this.satellites.forEach((s, i) => {
            if (!staticFrame) {
                s.userData.a += 0.0012 * s.userData.sp * 60 * 0.016;
                s.position.set(Math.cos(s.userData.a) * s.userData.rad - 1.5, s.userData.y + Math.sin(t * 0.8 + i) * 0.25, Math.sin(s.userData.a) * s.userData.rad * 0.55 - 2);
                const attr = s.userData.line.geometry.getAttribute('position');
                attr.setXYZ(0, s.position.x, s.position.y, s.position.z);
                attr.needsUpdate = true;
            }
        });
        if (this.stars) this.stars.rotation.y = t * 0.004;
        if (this.nebula) this.nebula.rotation.z = t * 0.005;
        if (this.dust && !staticFrame) this.dust.rotation.y = t * 0.01;
        if (this.sunGlow) this.sunGlow.material.opacity = 0.5 + Math.sin(t * 1.4) * 0.08;
        if (this.sunRing) this.sunRing.rotation.z = t * 0.05;
        // Subtle camera: mouse parallax (§4) + slow scroll depth drift
        this.mouse.x += (this.mouse.tx - this.mouse.x) * 0.04;
        this.mouse.y += (this.mouse.ty - this.mouse.y) * 0.04;
        const scrollDrift = Math.min(this.scrollY / 2000, 1) * 2.2;
        this.camera.position.x += (this.mouse.x * 1.1 - this.camera.position.x) * 0.03;
        this.camera.position.y += ((3.4 - this.mouse.y * 0.7 - scrollDrift * 0.5) - this.camera.position.y) * 0.03;
        this.camera.lookAt(-1, 0, -3);
        this.renderer.render(this.scene, this.camera);
    }

    animate() {
        if (this.isDestroyed) return;
        if (this.isVisible === false) { this.frameId = null; return; }
        this.frameId = requestAnimationFrame(() => this.animate());
        this.tick(false);
    }

    destroy() {
        this.isDestroyed = true;
        if (this.frameId) cancelAnimationFrame(this.frameId);
        this.frameId = null;
        if (this.ob) this.ob.disconnect();
        window.removeEventListener('mousemove', this.onMouse);
        window.removeEventListener('resize', this.onResize);
        window.removeEventListener('scroll', this.onScroll);
        document.removeEventListener('visibilitychange', this.onVis);
        if (this.renderer) { this.renderer.dispose(); this.renderer = null; }
        if (this.canvas) delete this.canvas.dataset.ssInit;
    }
}

export default SolarSystem;
