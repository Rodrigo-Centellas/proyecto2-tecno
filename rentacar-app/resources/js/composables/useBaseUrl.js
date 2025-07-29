export function useBaseUrl() {
    const baseUrl = '/inf513/grupo20sa/proyecto2/proyecto2-tecno/rentacar-app/public';
    
    const url = (path) => {
        return baseUrl + path;
    };
    
    return {
        baseUrl,
        url
    };
}