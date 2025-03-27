import path from "path";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import { watch } from "vite-plugin-watch";
import tailwindcss from "@tailwindcss/vite";
import autoimport from "unplugin-auto-import/vite";
import components from "unplugin-vue-components/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/js/app.ts"],
            refresh: true,
        }),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        autoimport({
            vueTemplate: true,
            include: [
                /\.vue$/,
                /\.vue\?vue/, // .vue
            ],
            dts: "./resources/js/types/auto-imports.d.ts",
            imports: [
                "vue",
                "@vueuse/core",
                {
                    "@inertiajs/vue3": ["router", "useForm", "usePage", "Link"],
                    "momentum-trail": ["route", "current"],
                },
            ],
            dirs: ["./resources/js"],
        }),
        components({
            dirs: ["resources/js"],
            dts: "resources/js/types/components.d.ts",
            resolvers: [
                (name: string) => {
                    const components = ["Link"];

                    if (components.includes(name)) {
                        return {
                            name: name,
                            from: "@inertiajs/vue3",
                        };
                    }
                },
            ],
        }),
        watch({
            pattern: "app/{Data,Enums}/**/*.php",
            command: "php artisan typescript:transform",
        }),
        watch({
            pattern: "routes/*.php",
            command: "php artisan trail:generate",
        }),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./resources/js"),
        },
    },
});
