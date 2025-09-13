/**
 * Shan Provider Configuration
 * 
 * This file contains the configuration for the Shan provider integration.
 * Update these values according to your provider settings.
 */

const SHAN_CONFIG = {
    // Provider Information
    PROVIDER_NAME: 'Shan Provider',
    PROVIDER_URL: 'https://luckymillion.pro/api',
    CALLBACK_URL: 'https://ponewine20x.xyz',
    
    // Agent Credentials
    AGENT_CODE: 'A3H4',
    AGENT_NAME: 'TestShanAgent',
    SECRET_KEY: 'HyrmLxMg4rvOoTZ',
    
    // User Information
    USERNAME: 'AG10726478',
    PASSWORD: 'delightmyanmar',
    DEFAULT_AMOUNT: 8000,
    
    // API Endpoints
    LAUNCH_GAME_ENDPOINT: '/api/client/launch-game',
    GET_BALANCE_ENDPOINT: '/api/client/get-balance',
    
    // Game Types
    GAME_TYPES: {
        SLOT: 'slot',
        LIVE: 'live',
        TABLE: 'table',
        FISHING: 'fishing'
    },
    
    // Product Codes
    PRODUCT_CODES: {
        PG_SOFT: 1007,
        PROVIDER_1: 1221,
        PROVIDER_2: 1040,
        PROVIDER_3: 1046,
        PROVIDER_4: 1004
    },
    
    // Currency
    CURRENCY: 'MMK',
    
    // Default Configuration for Client Launch Game
    getDefaultConfig() {
        return {
            apiUrl: this.PROVIDER_URL + this.LAUNCH_GAME_ENDPOINT,
            agentCode: this.AGENT_CODE,
            timeout: 30
        };
    },
    
    // Get launch game URL with parameters
    getLaunchGameUrl(memberAccount, balance, productCode, gameType) {
        return `${this.CALLBACK_URL}/?user_name=${encodeURIComponent(memberAccount)}&balance=${balance}&product_code=${productCode}&game_type=${gameType}&agent_code=${this.AGENT_CODE}`;
    },
    
    // Validate agent code
    isValidAgentCode(agentCode) {
        return agentCode === this.AGENT_CODE;
    },
    
    // Get product code name
    getProductCodeName(productCode) {
        const names = {
            [this.PRODUCT_CODES.PG_SOFT]: 'PG Soft',
            [this.PRODUCT_CODES.PROVIDER_1]: 'Provider 1',
            [this.PRODUCT_CODES.PROVIDER_2]: 'Provider 2',
            [this.PRODUCT_CODES.PROVIDER_3]: 'Provider 3',
            [this.PRODUCT_CODES.PROVIDER_4]: 'Provider 4'
        };
        return names[productCode] || 'Unknown';
    }
};

// Example usage:
/*
// Initialize client with Shan configuration
const gameClient = new ClientLaunchGame(SHAN_CONFIG.getDefaultConfig());

// Launch a game using Shan credentials
gameClient.launchSlotGame(SHAN_CONFIG.USERNAME, SHAN_CONFIG.PRODUCT_CODES.PG_SOFT, SHAN_CONFIG.AGENT_NAME);

// Get launch game URL
const gameUrl = SHAN_CONFIG.getLaunchGameUrl(
    SHAN_CONFIG.USERNAME,
    SHAN_CONFIG.DEFAULT_AMOUNT,
    SHAN_CONFIG.PRODUCT_CODES.PG_SOFT,
    SHAN_CONFIG.GAME_TYPES.SLOT
);

// Validate agent code
if (SHAN_CONFIG.isValidAgentCode('A3H4')) {
    console.log('Valid agent code');
}

// Get product name
console.log(SHAN_CONFIG.getProductCodeName(1007)); // "PG Soft"
*/

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SHAN_CONFIG;
} 