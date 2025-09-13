/**
 * Client Launch Game Library
 * 
 * This library provides easy integration with the provider launch game API
 * for client sites to launch games without authentication.
 */

class ClientLaunchGame {
    constructor(config = {}) {
        this.apiUrl = config.apiUrl || 'https://luckymillion.pro/api/client/launch-game';
        this.agentCode = config.agentCode || 'A3H4';
        this.onSuccess = config.onSuccess || this.defaultOnSuccess;
        this.onError = config.onError || this.defaultOnError;
        this.onLoading = config.onLoading || this.defaultOnLoading;
    }

    /**
     * Launch a game with the given parameters
     * @param {Object} gameData - Game launch parameters
     * @param {string} gameData.product_code - Product code
     * @param {string} gameData.game_type - Game type
     * @param {string} gameData.member_account - Member account
     * @param {number} gameData.balance - User balance
     * @param {string} [gameData.nickname] - Optional nickname
     * @returns {Promise<Object>} - Response with game URL
     */
    async launchGame(gameData) {
        try {
            // Show loading state
            this.onLoading(true);

                    const payload = {
            agent_code: this.agentCode,
            product_code: gameData.product_code,
            game_type: gameData.game_type,
            member_account: gameData.member_account,
            balance: gameData.balance,
            ...(gameData.nickname && { nickname: gameData.nickname })
        };

            const response = await fetch(this.apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (response.ok && result.code === 200) {
                this.onSuccess(result);
                return result;
            } else {
                throw new Error(result.message || 'Launch failed');
            }
        } catch (error) {
            this.onError(error);
            throw error;
        } finally {
            this.onLoading(false);
        }
    }

    /**
     * Launch a slot game
     * @param {string} memberAccount - Member account
     * @param {number} balance - User balance
     * @param {number} productCode - Product code
     * @param {string} [nickname] - Optional nickname
     * @returns {Promise<Object>} - Response with game URL
     */
    async launchSlotGame(memberAccount, balance, productCode = 1007, nickname = null) {
        return this.launchGame({
            product_code: productCode,
            game_type: 'slot',
            member_account: memberAccount,
            balance,
            nickname
        });
    }

    /**
     * Launch a live casino game
     * @param {string} memberAccount - Member account
     * @param {number} balance - User balance
     * @param {number} productCode - Product code
     * @param {string} [nickname] - Optional nickname
     * @returns {Promise<Object>} - Response with game URL
     */
    async launchLiveGame(memberAccount, balance, productCode = 1221, nickname = null) {
        return this.launchGame({
            product_code: productCode,
            game_type: 'live',
            member_account: memberAccount,
            balance,
            nickname
        });
    }

    /**
     * Launch a table game
     * @param {string} memberAccount - Member account
     * @param {number} balance - User balance
     * @param {number} productCode - Product code
     * @param {string} [nickname] - Optional nickname
     * @returns {Promise<Object>} - Response with game URL
     */
    async launchTableGame(memberAccount, balance, productCode = 1040, nickname = null) {
        return this.launchGame({
            product_code: productCode,
            game_type: 'table',
            member_account: memberAccount,
            balance,
            nickname
        });
    }

    /**
     * Launch a fishing game
     * @param {string} memberAccount - Member account
     * @param {number} balance - User balance
     * @param {number} productCode - Product code
     * @param {string} [nickname] - Optional nickname
     * @returns {Promise<Object>} - Response with game URL
     */
    async launchFishingGame(memberAccount, balance, productCode = 1046, nickname = null) {
        return this.launchGame({
            product_code: productCode,
            game_type: 'fishing',
            member_account: memberAccount,
            balance,
            nickname
        });
    }

    /**
     * Default success handler - opens game in new window
     * @param {Object} result - API response
     */
    defaultOnSuccess(result) {
        console.log('Game launched successfully:', result);
        window.open(result.url, '_blank');
    }

    /**
     * Default error handler - logs error to console
     * @param {Error} error - Error object
     */
    defaultOnError(error) {
        console.error('Game launch failed:', error);
        alert('Failed to launch game: ' + error.message);
    }

    /**
     * Default loading handler - shows/hides loading indicator
     * @param {boolean} isLoading - Loading state
     */
    defaultOnLoading(isLoading) {
        const loadingElement = document.getElementById('loading');
        if (loadingElement) {
            loadingElement.style.display = isLoading ? 'block' : 'none';
        }
    }
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ClientLaunchGame;
}

// Example usage:
/*
// Initialize the client
const gameClient = new ClientLaunchGame({
    apiUrl: 'https://luckymillion.pro/api/client/launch-game',
    agentCode: 'A3H4',
    onSuccess: (result) => {
        console.log('Game launched:', result.url);
        window.open(result.url, '_blank');
    },
    onError: (error) => {
        console.error('Launch failed:', error);
        alert('Game launch failed: ' + error.message);
    },
    onLoading: (isLoading) => {
        document.getElementById('loading').style.display = isLoading ? 'block' : 'none';
    }
});

// Launch different types of games
gameClient.launchSlotGame('AG10726478', 8000, 1007, 'TestShanAgent');
gameClient.launchLiveGame('AG10726478', 8000, 1221, 'TestShanAgent');
gameClient.launchTableGame('AG10726478', 8000, 1040, 'TestShanAgent');
gameClient.launchFishingGame('AG10726478', 8000, 1046, 'TestShanAgent');

// Or launch with custom parameters
gameClient.launchGame({
    product_code: 1007,
    game_type: 'slot',
    member_account: 'AG10726478',
    balance: 8000,
    nickname: 'TestShanAgent'
});
*/ 