<script setup>
import { onMounted, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

const currentVisits = ref(0);
const userVisits = ref(0);

// Ruta base del proyecto
const basePath = "https://www.tecnoweb.org.bo/inf513/grupo20ssa/proyecto3/P2TecnoWeb/public";

const registerVisit = async () => {
    const page = usePage();
    const currentPath = `${basePath}${window.location.pathname.replace(/^\/+/, "")}`;
    const csrfToken = document.head.querySelector(
        'meta[name="csrf-token"]'
    ).content;

    try {
        const response = await fetch(`${basePath}/register-visit`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify({
                url: currentPath,
                nombre: page.props.title || "Página desconocida",
            }),
        });

        if (response.ok) {
            const data = await response.json();
            currentVisits.value = data.page_visits;
            userVisits.value = data.user_visits;
        } else {
            console.error("Error en la solicitud:", response.status);
        }
    } catch (error) {
        console.error("Error al registrar la visita:", error);
    }
};

onMounted(() => {
    registerVisit();
});
</script>

<template>
    <footer class="app-footer2">
        <p>Total de visitas a esta página: {{ currentVisits }}</p>
        <p>Tus visitas a esta página: {{ userVisits }}</p>
    </footer>
</template>

<style>
.app-footer2 {
    padding: 1rem;
    background-color: var(--primary-color);
    text-align: center;
    border-top: var(--border-color);
    font-size: var(--footer-size);
    color: var(--footer-color);
}
</style>
