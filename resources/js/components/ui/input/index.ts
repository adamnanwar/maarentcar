import { type VariantProps, cva } from 'class-variance-authority'

export { default as Input } from './Input.vue'

export const inputVariants = cva(
    'flex w-full rounded-md border border-input bg-transparent text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
    {
        variants: {
            size: {
                default: 'h-9 px-3 py-1',
                sm: 'h-8 px-2 py-1 text-xs',
                lg: 'h-10 px-4 py-2',
            },
        },
        defaultVariants: {
            size: 'default',
        },
    },
)

export type InputVariants = VariantProps<typeof inputVariants>
