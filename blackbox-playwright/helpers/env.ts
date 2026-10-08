function required(name: string): string {
  const value = process.env[name];
  if (!value) {
    throw new Error(`Environment variable ${name} is missing. Copy .env.test.example to .env.test first.`);
  }
  return value;
}

export const ENV = {
  baseURL: process.env.BASE_URL ?? 'http://127.0.0.1:8000',
  adminEmail: () => required('ADMIN_EMAIL'),
  adminPassword: () => required('ADMIN_PASSWORD'),
  customerEmail: () => required('CUSTOMER_EMAIL'),
  customerPassword: () => required('CUSTOMER_PASSWORD'),
  laravelLogPath: process.env.LARAVEL_LOG_PATH ?? '../storage/logs/laravel.log',
};
