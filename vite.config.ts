import path from "path";
import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import autoimport from "unplugin-auto-import/vite";
import components from "unplugin-vue-components/vite";
import { run } from "vite-plugin-run";

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
        run([
            {
                name: "wayfinder",
                run: ["php", "artisan", "wayfinder:generate"],
                pattern: ["routes/*.php", "app/**/Http/**/*.php"],
            },
            {
                name: "typescript",
                run: ["php", "artisan", "typescript:transform"],
                pattern: ["app/{Data,Enums}/**/*.php"],
            },
        ]),
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
                },
            ],

            dirs: [
                "./resources/js",
                "./resources/js/actions/App/Http/Controllers/index.ts",
            ],
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
    ],
});
