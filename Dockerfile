FROM php:apache
COPY ./php-scripts/ /var/www/html/
ENV JELLYFIN_BASE_URL=default_jellyfin_base_url \
    JELLYFIN_API_KEY=default_jellyfin_api_key \
    LIDARR_API_URL=default_lidarr_api_url \
    LIDARR_API_KEY=default_lidarr_api_key \
    LIDARR_ROOT_FOLDER=default_lidarr_root_folder \
    LIDARR_QUALITY=1 \
    LIDARR_METADATA=1 \
    ENABLE_DISCORD_HOOK=true \
    DISCORD_WEBHOOK=default_discord_webhook \
    DISCORD_ICON=default_discord_icon \
    DISCORD_URL=default_discord_url 	
CMD ["apache2-foreground"]
