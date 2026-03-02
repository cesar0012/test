<template>
    <div class="app-shell">
        <header class="app-header">
            <h1>Track Manager</h1>
            <nav class="tabs">
                <button 
                    :class="['tab-btn', { active: activeTab === 'add' }]" 
                    @click="activeTab = 'add'"
                >
                    Add Track
                </button>
                <button 
                    :class="['tab-btn', { active: activeTab === 'all' }]" 
                    @click="activeTab = 'all'"
                >
                    All Tracks
                </button>
            </nav>
        </header>

        <main class="app-content">
            <div v-show="activeTab === 'add'" class="tab-pane">
                <TrackForm
                    :editing="editingTrack"
                    @saved="onTrackSaved"
                    @cancel="editingTrack = null"
                />
                
                <section class="recent-tracks-section">
                    <h3 class="section-title">Recently Added</h3>
                    <TrackTable
                        :tracks="recentTracks"
                        :compact="true"
                        @edit="onEditRequest"
                    />
                </section>
            </div>

            <div v-show="activeTab === 'all'" class="tab-pane">
                <TrackTable
                    :tracks="tracks"
                    :with-controls="true"
                    @edit="onEditRequest"
                />
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import trackApi from './services/trackApi.js'
import TrackForm from './components/TrackForm.vue'
import TrackTable from './components/TrackTable.vue'

const tracks = ref([])
const activeTab = ref('add')
const editingTrack = ref(null)

const recentTracks = computed(() => {
    return [...tracks.value].slice(-5).reverse()
})

async function loadTracks() {
    try {
        const { data } = await trackApi.getTracks()
        tracks.value = data
    } catch (err) {
        tracks.value = []
    }
}

function onEditRequest(track) {
    editingTrack.value = { ...track }
    activeTab.value = 'add'
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function onTrackSaved() {
    editingTrack.value = null
    loadTracks()
}

onMounted(loadTracks)
</script>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
    background-color: #f4f6f8;
    color: #333;
    line-height: 1.5;
}

.app-shell {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.app-header {
    margin-bottom: 1.5rem;
    border-bottom: 1px solid #ddd;
    padding-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.app-header h1 {
    font-size: 1.5rem;
    font-weight: 600;
    color: #222;
}

.tabs {
    display: flex;
    gap: 0.5rem;
}

.tab-btn {
    background: transparent;
    border: 1px solid transparent;
    border-bottom: 3px solid transparent;
    padding: 0.5rem 0.75rem;
    font-size: 0.95rem;
    color: #666;
    cursor: pointer;
}

.tab-btn:hover {
    color: #333;
}

.tab-btn.active {
    color: #0056b3;
    border-bottom-color: #0056b3;
}

.recent-tracks-section {
    margin-top: 2rem;
}

.section-title {
    font-size: 1.15rem;
    color: #444;
    margin-bottom: 1rem;
}

@media (max-width: 640px) {
    .app-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .tabs {
        width: 100%;
        border-bottom: 1px solid #ddd;
    }
}
</style>
