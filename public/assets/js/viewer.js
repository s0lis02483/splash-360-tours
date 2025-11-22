// FILE: /public/assets/js/viewer.js

/**
 * Splash360 Viewer
 * Simple 360° panorama viewer implementation
 * No external dependencies - pure JavaScript
 */

class Splash360Viewer {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error('Container not found:', containerId);
            return;
        }

        this.tourData = JSON.parse(this.container.dataset.tour);
        this.currentSceneIndex = 0;
        this.yaw = 0;
        this.pitch = 0;
        this.fov = 75;
        this.isDragging = false;
        this.lastX = 0;
        this.lastY = 0;

        this.canvas = null;
        this.ctx = null;
        this.image = null;
    }

    init() {
        this.createCanvas();
        this.setupControls();
        this.setupSceneNavigator();
        this.loadScene(0);
        this.startRenderLoop();
    }

    createCanvas() {
        this.canvas = document.createElement('canvas');
        this.canvas.width = this.container.offsetWidth;
        this.canvas.height = this.container.offsetHeight;
        this.container.appendChild(this.canvas);
        this.ctx = this.canvas.getContext('2d');

        // Handle resize
        window.addEventListener('resize', () => {
            this.canvas.width = this.container.offsetWidth;
            this.canvas.height = this.container.offsetHeight;
            this.render();
        });

        // Mouse controls
        this.canvas.addEventListener('mousedown', this.onMouseDown.bind(this));
        this.canvas.addEventListener('mousemove', this.onMouseMove.bind(this));
        this.canvas.addEventListener('mouseup', this.onMouseUp.bind(this));
        this.canvas.addEventListener('mouseleave', this.onMouseUp.bind(this));

        // Touch controls
        this.canvas.addEventListener('touchstart', this.onTouchStart.bind(this));
        this.canvas.addEventListener('touchmove', this.onTouchMove.bind(this));
        this.canvas.addEventListener('touchend', this.onTouchEnd.bind(this));

        // Mouse wheel zoom
        this.canvas.addEventListener('wheel', this.onWheel.bind(this));

        // Click for hotspots
        this.canvas.addEventListener('click', this.onClick.bind(this));
    }

    setupControls() {
        const zoomIn = document.getElementById('zoom-in');
        const zoomOut = document.getElementById('zoom-out');
        const fullscreen = document.getElementById('fullscreen');

        if (zoomIn) zoomIn.addEventListener('click', () => this.zoom(-5));
        if (zoomOut) zoomOut.addEventListener('click', () => this.zoom(5));
        if (fullscreen) fullscreen.addEventListener('click', () => this.toggleFullscreen());
    }

    setupSceneNavigator() {
        const sceneItems = document.querySelectorAll('.scene-item');
        sceneItems.forEach((item, index) => {
            item.addEventListener('click', () => this.loadScene(index));
        });
    }

    loadScene(index) {
        if (index < 0 || index >= this.tourData.scenes.length) return;

        this.currentSceneIndex = index;
        const scene = this.tourData.scenes[index];

        // Update active state in navigator
        document.querySelectorAll('.scene-item').forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });

        // Load image
        this.image = new Image();
        this.image.crossOrigin = 'anonymous';
        this.image.onload = () => {
            this.yaw = scene.initial_yaw || 0;
            this.pitch = scene.initial_pitch || 0;
            this.render();
        };
        this.image.src = scene.image_url;
    }

    render() {
        if (!this.image || !this.image.complete) return;

        const w = this.canvas.width;
        const h = this.canvas.height;

        this.ctx.clearRect(0, 0, w, h);

        // Simple equirectangular projection
        // This is a simplified version - production would use WebGL for better performance
        const fovRad = (this.fov * Math.PI) / 180;
        const yawRad = (this.yaw * Math.PI) / 180;
        const pitchRad = (this.pitch * Math.PI) / 180;

        // Draw the panorama using a simplified mapping
        // For each pixel on screen, calculate corresponding pixel on panorama
        const imgW = this.image.width;
        const imgH = this.image.height;

        for (let y = 0; y < h; y += 2) { // Step by 2 for performance
            for (let x = 0; x < w; x += 2) {
                // Convert screen coords to normalized coords (-1 to 1)
                const nx = (x / w) * 2 - 1;
                const ny = (y / h) * 2 - 1;

                // Calculate viewing angle
                const theta = yawRad + nx * fovRad / 2;
                const phi = pitchRad + ny * fovRad / 2;

                // Map to panorama coords
                const u = ((theta / (Math.PI * 2)) + 0.5) % 1;
                const v = ((phi / Math.PI) + 0.5);

                // Clamp v
                const vClamped = Math.max(0, Math.min(1, v));

                const px = Math.floor(u * imgW);
                const py = Math.floor(vClamped * imgH);

                // Get pixel color
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = imgW;
                tempCanvas.height = imgH;
                const tempCtx = tempCanvas.getContext('2d');
                tempCtx.drawImage(this.image, 0, 0);

                try {
                    const pixelData = tempCtx.getImageData(px, py, 1, 1).data;
                    this.ctx.fillStyle = `rgb(${pixelData[0]},${pixelData[1]},${pixelData[2]})`;
                    this.ctx.fillRect(x, y, 2, 2);
                } catch (e) {
                    // CORS or other error - use simplified rendering
                    this.ctx.drawImage(this.image, 0, 0, w, h);
                    break;
                }
            }
        }

        // Simplified alternative: just draw stretched image (fallback)
        // This provides basic functionality even if pixel-perfect rendering fails
        if (this.ctx.fillStyle === 'rgb(0,0,0)') {
            this.ctx.save();
            this.ctx.translate(w / 2, h / 2);
            const scale = Math.max(w / this.image.width, h / this.image.height);
            this.ctx.scale(scale, scale);
            this.ctx.translate(-this.image.width / 2, -this.image.height / 2);
            this.ctx.drawImage(this.image, 0, 0);
            this.ctx.restore();
        }

        this.renderHotspots();
    }

    renderHotspots() {
        const scene = this.tourData.scenes[this.currentSceneIndex];
        if (!scene || !scene.hotspots) return;

        scene.hotspots.forEach(hotspot => {
            const pos = this.project3DTo2D(hotspot.yaw, hotspot.pitch);
            if (pos) {
                // Draw hotspot
                this.ctx.save();
                this.ctx.translate(pos.x, pos.y);
                this.ctx.beginPath();
                this.ctx.arc(0, 0, 20, 0, Math.PI * 2);
                this.ctx.fillStyle = 'rgba(102, 126, 234, 0.9)';
                this.ctx.fill();
                this.ctx.strokeStyle = '#fff';
                this.ctx.lineWidth = 2;
                this.ctx.stroke();

                // Draw icon
                this.ctx.fillStyle = '#fff';
                this.ctx.font = 'bold 16px Arial';
                this.ctx.textAlign = 'center';
                this.ctx.textBaseline = 'middle';
                const icon = hotspot.type === 'navigation' ? '→' :
                            hotspot.type === 'info' ? 'i' : '🔗';
                this.ctx.fillText(icon, 0, 0);
                this.ctx.restore();
            }
        });
    }

    project3DTo2D(yaw, pitch) {
        // Project 3D position to 2D screen coordinates
        const yawRad = (this.yaw * Math.PI) / 180;
        const pitchRad = (this.pitch * Math.PI) / 180;
        const hotspotYawRad = (yaw * Math.PI) / 180;
        const hotspotPitchRad = (pitch * Math.PI) / 180;

        const deltaYaw = hotspotYawRad - yawRad;
        const deltaPitch = hotspotPitchRad - pitchRad;

        // Check if hotspot is in view
        const fovRad = (this.fov * Math.PI) / 180;
        if (Math.abs(deltaYaw) > fovRad || Math.abs(deltaPitch) > fovRad) {
            return null;
        }

        const x = this.canvas.width / 2 + (deltaYaw / fovRad) * this.canvas.width;
        const y = this.canvas.height / 2 + (deltaPitch / fovRad) * this.canvas.height;

        return { x, y };
    }

    onMouseDown(e) {
        this.isDragging = true;
        this.lastX = e.clientX;
        this.lastY = e.clientY;
    }

    onMouseMove(e) {
        if (!this.isDragging) return;

        const deltaX = e.clientX - this.lastX;
        const deltaY = e.clientY - this.lastY;

        this.yaw -= deltaX * 0.2;
        this.pitch += deltaY * 0.2;

        // Clamp pitch
        this.pitch = Math.max(-90, Math.min(90, this.pitch));

        this.lastX = e.clientX;
        this.lastY = e.clientY;

        this.render();
    }

    onMouseUp() {
        this.isDragging = false;
    }

    onTouchStart(e) {
        if (e.touches.length === 1) {
            this.isDragging = true;
            this.lastX = e.touches[0].clientX;
            this.lastY = e.touches[0].clientY;
        }
    }

    onTouchMove(e) {
        if (!this.isDragging || e.touches.length !== 1) return;

        e.preventDefault();
        const deltaX = e.touches[0].clientX - this.lastX;
        const deltaY = e.touches[0].clientY - this.lastY;

        this.yaw -= deltaX * 0.2;
        this.pitch += deltaY * 0.2;
        this.pitch = Math.max(-90, Math.min(90, this.pitch));

        this.lastX = e.touches[0].clientX;
        this.lastY = e.touches[0].clientY;

        this.render();
    }

    onTouchEnd() {
        this.isDragging = false;
    }

    onWheel(e) {
        e.preventDefault();
        this.zoom(e.deltaY > 0 ? 5 : -5);
    }

    onClick(e) {
        // Check if clicked on hotspot
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const scene = this.tourData.scenes[this.currentSceneIndex];
        if (!scene || !scene.hotspots) return;

        for (const hotspot of scene.hotspots) {
            const pos = this.project3DTo2D(hotspot.yaw, hotspot.pitch);
            if (pos) {
                const dist = Math.sqrt((x - pos.x) ** 2 + (y - pos.y) ** 2);
                if (dist < 20) {
                    this.handleHotspotClick(hotspot);
                    break;
                }
            }
        }
    }

    handleHotspotClick(hotspot) {
        if (hotspot.type === 'navigation' && hotspot.target_scene_id) {
            // Find scene index by ID
            const index = this.tourData.scenes.findIndex(s => s.id == hotspot.target_scene_id);
            if (index >= 0) {
                this.loadScene(index);
            }
        } else if (hotspot.type === 'info') {
            this.showInfoPanel(hotspot.label, hotspot.description);
        } else if (hotspot.type === 'link' && hotspot.external_url) {
            window.open(hotspot.external_url, '_blank');
        }
    }

    showInfoPanel(title, description) {
        const panel = document.getElementById('info-panel');
        if (!panel) return;

        document.getElementById('info-title').textContent = title || 'Information';
        document.getElementById('info-description').textContent = description || '';
        panel.style.display = 'block';

        document.getElementById('close-info').onclick = () => {
            panel.style.display = 'none';
        };
    }

    zoom(delta) {
        this.fov += delta;
        this.fov = Math.max(30, Math.min(120, this.fov));
        this.render();
    }

    toggleFullscreen() {
        if (!document.fullscreenElement) {
            this.container.parentElement.requestFullscreen();
        } else {
            document.exitFullscreen();
        }
    }

    startRenderLoop() {
        // Render continuously for smooth experience
        setInterval(() => {
            if (!this.isDragging) {
                this.render();
            }
        }, 100);
    }
}
