<template>
    <div class="form-container" :class="{ 'is-editing': isEditing }">
        <div class="form-header">
            <h2>
                {{ isEditing ? 'Edit Track' : 'Add New Track' }}
            </h2>
            <p class="subtitle">{{ isEditing ? 'Update the details below.' : 'Fill in the information to add a track.' }}</p>
        </div>
        
        <form class="track-form" @submit.prevent="handleSubmit">
            <div class="field-group">
                <div class="field" :class="{ 'has-error': fieldErrors.title }">
                    <label for="tf-title">Track Title</label>
                    <div class="input-wrapper">
                        <input id="tf-title" v-model="form.title" type="text" placeholder="e.g. Bohemian Rhapsody" required />
                    </div>
                    <span v-if="fieldErrors.title" class="field-error">{{ fieldErrors.title }}</span>
                </div>
                <div class="field" :class="{ 'has-error': fieldErrors.artist }">
                    <label for="tf-artist">Artist Name</label>
                    <div class="input-wrapper">
                        <input id="tf-artist" v-model="form.artist" type="text" placeholder="e.g. Queen" required />
                    </div>
                    <span v-if="fieldErrors.artist" class="field-error">{{ fieldErrors.artist }}</span>
                </div>
            </div>
            <div class="field-group">
                <div class="field" :class="{ 'has-error': fieldErrors.duration }">
                    <label for="tf-duration">Duration <span class="hint">(mm:ss)</span></label>
                    <div class="input-wrapper">
                        <input
                            id="tf-duration"
                            v-model="durationDisplay"
                            type="text"
                            placeholder="e.g. 5:55"
                            required
                            @blur="validateDuration"
                        />
                    </div>
                    <span v-if="fieldErrors.duration" class="field-error">{{ fieldErrors.duration }}</span>
                </div>
                <div class="field" :class="{ 'has-error': fieldErrors.isrc }">
                    <label for="tf-isrc">ISRC <span class="hint">(Optional)</span></label>
                    <div class="input-wrapper">
                        <input
                            id="tf-isrc"
                            v-model="form.isrc"
                            type="text"
                            placeholder="US-ABC-24-00001"
                        />
                    </div>
                    <span v-if="fieldErrors.isrc" class="field-error">{{ fieldErrors.isrc }}</span>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary" :disabled="submitting">
                    {{ isEditing ? 'Save Changes' : 'Create Track' }}
                    <span v-if="submitting" class="spinner"></span>
                </button>
                <button v-if="isEditing" type="button" class="btn-secondary" @click="cancel">Cancel</button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import trackApi from '../services/trackApi.js'

const ISRC_PATTERN = /^[A-Z]{2}-[A-Z0-9]{3}-\d{2}-\d{5}$/

const props = defineProps({
    editing: { type: Object, default: null }
})

const emit = defineEmits(['saved', 'cancel'])

const form = ref(emptyForm())
const durationDisplay = ref('')
const fieldErrors = ref({})
const submitting = ref(false)

const isEditing = computed(() => props.editing !== null)

watch(() => props.editing, (track) => {
    if (track) {
        form.value = { ...track }
        durationDisplay.value = secondsToDisplay(track.duration)
        fieldErrors.value = {}
    } else {
        resetForm()
    }
}, { immediate: true })

function emptyForm() {
    return { title: '', artist: '', duration: 0, isrc: '' }
}

function resetForm() {
    form.value = emptyForm()
    durationDisplay.value = ''
    fieldErrors.value = {}
}

function secondsToDisplay(s) {
    if (!s && s !== 0) return ''
    const min = Math.floor(s / 60)
    const sec = s % 60
    return `${min}:${String(sec).padStart(2, '0')}`
}

function parseDuration(raw) {
    const parts = raw.split(':')
    if (parts.length === 2) {
        const min = parseInt(parts[0], 10)
        const sec = parseInt(parts[1], 10)
        if (!isNaN(min) && !isNaN(sec) && sec < 60 && sec >= 0 && min >= 0) {
            return min * 60 + sec
        }
    }
    if (parts.length === 1) {
        const val = parseInt(parts[0], 10)
        if (!isNaN(val) && val > 0) return val
    }
    return null
}

function validateDuration() {
    if (!durationDisplay.value) return
    const parsed = parseDuration(durationDisplay.value)
    if (parsed === null) {
        fieldErrors.value.duration = 'Use mm:ss format or seconds'
    } else {
        delete fieldErrors.value.duration
        form.value.duration = parsed
    }
}

function validateLocal() {
    const errs = {}
    if (!form.value.title.trim()) errs.title = 'Required'
    if (!form.value.artist.trim()) errs.artist = 'Required'

    const dur = parseDuration(durationDisplay.value)
    if (dur === null || dur <= 0) errs.duration = 'Use mm:ss format or seconds'
    else form.value.duration = dur

    if (form.value.isrc && !ISRC_PATTERN.test(form.value.isrc)) {
        errs.isrc = 'Format: XX-XXX-00-00000'
    }

    fieldErrors.value = errs
    return Object.keys(errs).length === 0
}

async function handleSubmit() {
    if (!validateLocal()) return
    submitting.value = true

    const payload = {
        title: form.value.title,
        artist: form.value.artist,
        duration: form.value.duration,
        isrc: form.value.isrc || null
    }

    try {
        if (isEditing.value) {
            await trackApi.updateTrack(form.value.id, payload)
        } else {
            await trackApi.createTrack(payload)
        }
        resetForm()
        emit('saved')
    } catch (err) {
        if (err.response?.data?.errors) {
            fieldErrors.value = err.response.data.errors
        }
    } finally {
        submitting.value = false
    }
}

function cancel() {
    resetForm()
    emit('cancel')
}
</script>

<style scoped>
.form-container {
    background: #fff;
    border: 1px solid #ddd;
    padding: 1.5rem;
    border-radius: 4px;
    margin-bottom: 2rem;
}

.form-header {
    margin-bottom: 1.5rem;
}

.form-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #222;
    margin-bottom: 0.25rem;
}

.form-header .subtitle {
    font-size: 0.9rem;
    color: #666;
}

.field-group {
    display: flex;
    gap: 1.5rem;
    margin-bottom: 1rem;
}

.field {
    flex: 1;
}

@media (max-width: 640px) {
    .field-group {
        flex-direction: column;
        gap: 1rem;
    }
}

.field label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: #444;
    margin-bottom: 0.4rem;
}

.hint {
    font-weight: normal;
    color: #888;
}

.input-wrapper {
    position: relative;
}

.field input {
    width: 100%;
    padding: 0.6rem 0.75rem;
    border: 1px solid #ccc;
    border-radius: 3px;
    font-size: 0.95rem;
    color: #333;
    background: #fff;
}

.field input::placeholder {
    color: #aaa;
}

.field input:focus {
    outline: none;
    border-color: #0056b3;
}

.field.has-error input {
    border-color: #d9534f;
    background-color: #fdf7f7;
}

.field-error {
    display: block;
    color: #d9534f;
    font-size: 0.8rem;
    margin-top: 0.3rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-primary {
    background: #0056b3;
    color: #fff;
    border: 1px solid #004494;
    padding: 0.6rem 1.2rem;
    border-radius: 3px;
    font-size: 0.95rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary:hover:not(:disabled) {
    background: #004494;
}

.btn-primary:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.btn-secondary {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ccc;
    padding: 0.6rem 1.2rem;
    border-radius: 3px;
    font-size: 0.95rem;
    cursor: pointer;
}

.btn-secondary:hover {
    background: #e9ecef;
}

.spinner {
    display: inline-block;
    width: 0.9rem;
    height: 0.9rem;
    border: 2px solid rgba(255,255,255,0.4);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
