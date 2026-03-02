import axios from 'axios'

const http = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
    headers: {
        'Content-Type': 'application/json'
    }
})

export default {
    getTracks() {
        return http.get('/tracks')
    },

    createTrack(data) {
        return http.post('/tracks', data)
    },

    updateTrack(id, data) {
        return http.put(`/tracks/${id}`, data)
    }
}
