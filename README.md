☁️ Cloudlink

Cloudlink is a modular, microservice-based platform 🌐 designed to manage user authentication, notifications, dashboards, weather data, hyperlocal IoT data, and real-time communication. The architecture is event-driven, highly scalable, and built with both PHP and Python services 🐍🖥️.

📚 Table of Contents

    API Gateway

    User Service

    Notification Service

    Platform Service

    Weather Service

    Unified Service

    Python IoT Server

    Python Chat & Metrics Server

    Deployment

Future Plans

🚪 API Gateway

The API Gateway is the entry point for all client requests 🌟. It is fully custom-built 🛠️ and includes:

    🔑 Token-based authentication & session management

    ⏱️ Rate limiting using a bucket algorithm

    🗂️ Request aggregation & caching for high-performance

    🛡️ Route authentication & validation

    📝 Logging and monitoring

    🔗 Microservice routing (User, Notification, Platform, Weather, Unified)

    Note: All requests pass through the gateway, ensuring a single point for security, caching, and throttling 🔒⚡.

👤 User Service

    Handles all user-related operations including authentication 🔑 and account management:

    🔐 Login / Logout

    🛠️ Change password, email, phone number

    ✅ Account verification & multi-factor authentication

    🎯 Custom CAPTCHA validation

    🕒 Session management

    📊 Optional: Activity logging, account lockout & audit trails

    This service is designed to be secure, scalable, and extendable 🏗️.

📣 Notification Service

    Responsible for notifying users about system events 💌:

    📧 Send emails after registration, password change, or notifications from the platform

    🔔 Push notifications to Cloudlink frontend via WebSocket or Kafka (planned)

    📱 Planned SMS notifications

    ⚡ Event-driven: listens to messages from User Service or other microservices

    🖥️ Platform Service

    Provides all necessary dashboard content 📊:

        🖼️ Centralized dashboard for users

        📊 Data aggregation & visualization

        🔗 API endpoints for frontend consumption

        ⚙️ Provides content such as metrics, alerts, and reports

🌤️ Weather Service

Handles geospatial weather data 🌍:

    🗄️ Redis-based caching for fast retrieval

    📍 Nearby coordinate search, top cities, and popular datasets

    📈 Dataset of 10,000+ locations integrated with OpenWeatherMap

    🧭 Custom geospatial algorithm using Haversine formula to calculate distances & proximity

    ⚡ Provides APIs for real-time weather & historical data

    Haversine Algorithm: Calculates shortest distance between two points on Earth 🌎, essential for hyperlocal weather analysis 🌡️.

🔗 Unified Service

    Combines data from multiple sources 🌐:

    🛰️ Integrates hyperlocal IoT data (planned) & Weather Service data

    📊 Serves combined insights to Platform Service

    ⚡ Prepares aggregated data for analytics & notifications

🛠️ Python IoT Server

    Collects and processes IoT sensor data 🤖:

    📡 Receives data from local devices

    🧹 Performs initial data cleaning & transformation

    💾 Stores data in a format consumable by Unified Service

💬 Python Chat & Metrics Server

    Handles real-time communication & monitoring ⚡:

    💬 Chat server for frontend interactions

    📊 EDA (Exploratory Data Analysis) of incoming metrics

    📈 Metrics aggregation for Prometheus

    🐳 Dockerized for easy deployment & scaling

🚀 Deployment

    🐳 All microservices containerized using Docker

    🔗 Services communicate asynchronously via Kafka & WebSockets

    🗄️ Redis for caching & session storage

    🌐 API Gateway serves as the unified entry point for all requests

🌟 Future Plans

    🛰️ Hyperlocal IoT integration for enhanced environmental insights

    📱 SMS notification service for mobile users

    📊 Advanced analytics dashboard in Platform Service

    🤖 Expand Unified Service to include predictive analytics using AI/ML
