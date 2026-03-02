<template>
    <div class="table-wrapper">
        <div v-if="withControls" class="table-controls">
            <input 
                v-model="searchQuery" 
                type="text" 
                placeholder="Filter by title..." 
                class="search-input"
            />
        </div>

        <div v-if="filteredAndSortedTracks.length === 0" class="empty-state">
            <p>No tracks found.</p>
        </div>
        
        <div v-else class="table-container">
            <table>
                <thead>
                    <tr>
                        <th 
                            :class="{ sortable: withControls, active: sortColumn === 'title' }" 
                            @click="toggleSort('title')"
                        >
                            Title
                            <span v-if="withControls && sortColumn === 'title'" class="sort-icon">
                                {{ sortDesc ? '↓' : '↑' }}
                            </span>
                        </th>
                        <th>Artist</th>
                        <th v-if="!compact">Duration</th>
                        <th v-if="!compact">ISRC</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="track in filteredAndSortedTracks" :key="track.id">
                        <td class="font-medium">{{ track.title }}</td>
                        <td>{{ track.artist }}</td>
                        <td v-if="!compact">{{ formatDuration(track.duration) }}</td>
                        <td v-if="!compact" class="text-mono">{{ track.isrc || '—' }}</td>
                        <td class="actions-cell">
                            <button class="btn-edit" @click="$emit('edit', track)">Edit</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    tracks: { type: Array, required: true },
    compact: { type: Boolean, default: false },
    withControls: { type: Boolean, default: false }
})

defineEmits(['edit'])

const searchQuery = ref('')
const sortColumn = ref('title')
const sortDesc = ref(false)

const filteredAndSortedTracks = computed(() => {
    let result = [...props.tracks]

    if (props.withControls && searchQuery.value.trim() !== '') {
        const query = searchQuery.value.toLowerCase()
        result = result.filter(track => 
            track.title.toLowerCase().includes(query)
        )
    }

    if (props.withControls) {
        result.sort((a, b) => {
            let valA = a[sortColumn.value] || ''
            let valB = b[sortColumn.value] || ''
            
            if (typeof valA === 'string') valA = valA.toLowerCase()
            if (typeof valB === 'string') valB = valB.toLowerCase()

            if (valA < valB) return sortDesc.value ? 1 : -1
            if (valA > valB) return sortDesc.value ? -1 : 1
            return 0
        })
    }

    return result
})

function toggleSort(column) {
    if (!props.withControls) return
    if (sortColumn.value === column) {
        sortDesc.value = !sortDesc.value
    } else {
        sortColumn.value = column
        sortDesc.value = false
    }
}

function formatDuration(seconds) {
    const min = Math.floor(seconds / 60)
    const sec = seconds % 60
    return `${min}:${String(sec).padStart(2, '0')}`
}
</script>

<style scoped>
.table-wrapper {
    width: 100%;
}

.table-controls {
    margin-bottom: 1.5rem;
    display: flex;
    justify-content: flex-end;
}

.search-input {
    width: 100%;
    max-width: 320px;
    padding: 0.5rem 0.75rem;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 0.95rem;
    background: #fff;
}

.search-input:focus {
    outline: none;
    border-color: #0056b3;
}

.empty-state {
    padding: 3rem 1rem;
    text-align: center;
    background: #fff;
    border-radius: 3px;
    border: 1px solid #ddd;
    color: #666;
    font-size: 1rem;
}

.table-container {
    background: #fff;
    border-radius: 3px;
    border: 1px solid #ddd;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}

thead {
    background: #f8f9fa;
    border-bottom: 2px solid #ddd;
}

th {
    padding: 0.75rem 1rem;
    font-size: 0.85rem;
    font-weight: 600;
    color: #444;
    user-select: none;
}

th.sortable {
    cursor: pointer;
}

th.sortable:hover {
    background-color: #e9ecef;
}

th.active {
    color: #0056b3;
}

.sort-icon {
    display: inline-block;
    margin-left: 0.25rem;
    font-size: 0.9rem;
    vertical-align: middle;
}

td {
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    color: #333;
    border-bottom: 1px solid #eee;
}

tr:last-child td {
    border-bottom: none;
}

tbody tr:hover {
    background-color: #f1f3f5;
}

.font-medium {
    font-weight: 600;
    color: #222;
}

.text-mono {
    font-family: SFMono-Regular, Consolas, monospace;
    font-size: 0.85rem;
    color: #555;
    background: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 3px;
    border: 1px solid #eee;
}

.actions-cell {
    text-align: right;
    white-space: nowrap;
}

.btn-edit {
    background: #fff;
    border: 1px solid #ccc;
    color: #333;
    padding: 0.3rem 0.6rem;
    border-radius: 3px;
    font-size: 0.85rem;
    cursor: pointer;
}

.btn-edit:hover {
    background: #f8f9fa;
    border-color: #aaa;
}
</style>
