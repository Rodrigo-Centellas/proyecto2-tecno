import { ref, watchEffect } from 'vue'

const theme = ref(localStorage.getItem('theme') || 'auto') // niños, jovenes, adultos, auto
const fontSize = ref(localStorage.getItem('fontSize') || 'normal') // small, normal, large
const contrast = ref(localStorage.getItem('contrast') || 'normal') // normal, high

function applyTheme() {
    const hour = new Date().getHours()
    let computedTheme = theme.value

    if (theme.value === 'auto') {
        computedTheme = (hour >= 6 && hour < 18) ? 'day' : 'night'
    }

    document.body.className = `${computedTheme} ${fontSize.value} ${contrast.value}`
}

watchEffect(() => {
    localStorage.setItem('theme', theme.value)
    localStorage.setItem('fontSize', fontSize.value)
    localStorage.setItem('contrast', contrast.value)
    applyTheme()
})

export function useTheme() {
    return {
        theme,
        fontSize,
        contrast,
        setTheme: (val) => theme.value = val,
        setFontSize: (val) => fontSize.value = val,
        setContrast: (val) => contrast.value = val,
    }
}
