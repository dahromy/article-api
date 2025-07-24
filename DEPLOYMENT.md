# Deployment Guide

## Pre-deployment Checklist

1. **Environment Configuration**
   - [ ] Update `.env.prod` with production values
   - [ ] Generate new JWT keys: `php bin/console lexik:jwt:generate-keypair`
   - [ ] Set proper MongoDB connection string
   - [ ] Configure Redis if using for caching
   - [ ] Set proper CORS origins

2. **Security Checks**
   - [ ] Ensure APP_DEBUG=0 in production
   - [ ] Verify JWT keys are secure and not in version control
   - [ ] Check that APP_SECRET is unique and secure
   - [ ] Confirm HTTPS is enforced (SECURITY_FORCE_HTTPS=true)

3. **Database Setup**
   - [ ] Create MongoDB indexes: `php bin/console doctrine:mongodb:schema:create --index`
   - [ ] Verify database connectivity
   - [ ] Create initial admin user if needed

4. **Performance Optimization**
   - [ ] Install and configure Redis for caching (optional)
   - [ ] Enable OPcache in PHP
   - [ ] Configure proper rate limiting values
   - [ ] Set up database connection pooling

## Deployment Steps

### Docker Deployment

1. Build the Docker image:
   ```bash
   docker build -t article-api:latest .
   ```

2. Run with environment variables:
   ```bash
   docker run -d \
     --name article-api \
     -p 8000:8000 \
     -e APP_ENV=prod \
     -e MONGODB_URL=mongodb://mongo:27017 \
     -e APP_SECRET=your-secure-secret \
     article-api:latest
   ```

### Traditional Server Deployment

1. Upload files to server
2. Install dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. Set up environment:
   ```bash
   cp .env.prod .env.local
   # Edit .env.local with actual production values
   ```

4. Generate optimized autoloader and cache:
   ```bash
   composer dump-autoloader --optimize --no-dev
   php bin/console cache:clear --env=prod
   php bin/console cache:warmup --env=prod
   ```

5. Create database indexes:
   ```bash
   php bin/console doctrine:mongodb:schema:create --index
   ```

6. Set proper file permissions:
   ```bash
   chown -R www-data:www-data var/
   chmod -R 755 var/
   ```

## Health Checks

Configure your load balancer or monitoring system to use these endpoints:

- **Liveness**: `GET /health/live` - Always returns 200 if application is running
- **Readiness**: `GET /health/ready` - Returns 200 if application can serve requests
- **Health**: `GET /health` - Comprehensive health check including database

## Monitoring

1. **Logs**: Monitor application logs in `var/log/`
2. **Metrics**: API response times and error rates
3. **Database**: MongoDB connection status and query performance
4. **Cache**: Redis/filesystem cache hit rates
5. **Rate Limiting**: Monitor for excessive API usage

## Backup Strategy

1. **Database**: Regular MongoDB backups
2. **Application**: Backup uploaded files and configuration
3. **Security**: Backup JWT keys securely

## Troubleshooting

### Common Issues

1. **MongoDB Connection Failed**
   - Check connection string in MONGODB_URL
   - Verify network connectivity
   - Check MongoDB service status

2. **JWT Authentication Errors**
   - Verify JWT keys exist and are readable
   - Check JWT_TTL configuration
   - Ensure system time is synchronized

3. **High Memory Usage**
   - Check for memory leaks in long-running processes
   - Monitor OPcache usage
   - Review cache configuration

4. **Slow API Responses**
   - Check database indexes
   - Monitor query performance
   - Review cache hit rates
   - Check rate limiting configuration

### Performance Tuning

1. **PHP Configuration**
   ```ini
   memory_limit = 256M
   opcache.enable = 1
   opcache.memory_consumption = 128
   opcache.max_accelerated_files = 4000
   ```

2. **MongoDB Optimization**
   - Ensure proper indexes are created
   - Monitor query performance
   - Use read preferences for scaling

3. **Caching Strategy**
   - Use Redis for distributed caching
   - Configure appropriate TTL values
   - Monitor cache hit rates