/**
 * Client Integration Example for Game Launch API
 * 
 * This example shows how to integrate the game launch API into your client site.
 * Replace the API_BASE_URL with your actual API endpoint.
 */

const API_BASE_URL = 'https://your-domain.com/api'; // Replace with your actual domain

/**
 * Launch a game for a user
 * @param {Object} gameData - Game launch parameters
 * @param {string} gameData.agent_code - Agent code
 * @param {number} gameData.product_code - Product code
 * @param {string} gameData.game_type - Game type (e.g., 'slot', 'live', 'table')
 * @param {string} gameData.member_account - User's member account
 * @param {string} [gameData.nickname] - Optional nickname for the user
 * @returns {Promise<Object>} - Response with game URL
 */
async function launchGame(gameData) {
    try {
        const response = await fetch(`${API_BASE_URL}/client/launch-game`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify(gameData)
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(`API Error: ${result.message || 'Unknown error'}`);
        }

        return result;
    } catch (error) {
        console.error('Game launch failed:', error);
        throw error;
    }
}

/**
 * Example: Launch a slot game
 */
async function launchSlotGame() {
    try {
        const gameData = {
            agent_code: 'your_agent_code',
            product_code: 1007, // PG Soft
            game_type: 'slot',
            member_account: 'player123',
            nickname: 'Player 123'
        };

        const result = await launchGame(gameData);
        
        if (result.code === 200) {
            console.log('Game launched successfully!');
            console.log('Game URL:', result.url);
            
            // Open the game in a new window/tab
            window.open(result.url, '_blank');
            
            return result;
        } else {
            console.error('Launch failed:', result.message);
            return result;
        }
    } catch (error) {
        console.error('Error launching slot game:', error);
        throw error;
    }
}

/**
 * Example: Launch a live casino game
 */
async function launchLiveGame() {
    try {
        const gameData = {
            agent_code: 'your_agent_code',
            product_code: 1221,
            game_type: 'live',
            member_account: 'player123',
            nickname: 'Player 123'
        };

        const result = await launchGame(gameData);
        
        if (result.code === 200) {
            console.log('Live game launched successfully!');
            console.log('Game URL:', result.url);
            
            // Open the game in a new window/tab
            window.open(result.url, '_blank');
            
            return result;
        } else {
            console.error('Launch failed:', result.message);
            return result;
        }
    } catch (error) {
        console.error('Error launching live game:', error);
        throw error;
    }
}

/**
 * Example: Launch a table game
 */
async function launchTableGame() {
    try {
        const gameData = {
            agent_code: 'your_agent_code',
            product_code: 1040,
            game_type: 'table',
            member_account: 'player123',
            nickname: 'Player 123'
        };

        const result = await launchGame(gameData);
        
        if (result.code === 200) {
            console.log('Table game launched successfully!');
            console.log('Game URL:', result.url);
            
            // Open the game in a new window/tab
            window.open(result.url, '_blank');
            
            return result;
        } else {
            console.error('Launch failed:', result.message);
            return result;
        }
    } catch (error) {
        console.error('Error launching table game:', error);
        throw error;
    }
}

/**
 * Example: Launch game with custom parameters
 * @param {string} memberAccount - User's member account
 * @param {number} productCode - Product code
 * @param {string} gameType - Game type
 * @param {string} [nickname] - Optional nickname
 */
async function launchCustomGame(memberAccount, productCode, gameType, nickname = null) {
    try {
        const gameData = {
            agent_code: 'your_agent_code',
            product_code: productCode,
            game_type: gameType,
            member_account: memberAccount,
            ...(nickname && { nickname })
        };

        const result = await launchGame(gameData);
        
        if (result.code === 200) {
            console.log('Custom game launched successfully!');
            console.log('Game URL:', result.url);
            
            // Open the game in a new window/tab
            window.open(result.url, '_blank');
            
            return result;
        } else {
            console.error('Launch failed:', result.message);
            return result;
        }
    } catch (error) {
        console.error('Error launching custom game:', error);
        throw error;
    }
}

/**
 * Example: Handle game launch with UI feedback
 * @param {Object} gameData - Game launch parameters
 * @param {Function} onSuccess - Success callback
 * @param {Function} onError - Error callback
 */
async function launchGameWithUI(gameData, onSuccess, onError) {
    try {
        // Show loading state
        console.log('Launching game...');
        
        const result = await launchGame(gameData);
        
        if (result.code === 200) {
            console.log('Game launched successfully!');
            onSuccess && onSuccess(result);
        } else {
            console.error('Launch failed:', result.message);
            onError && onError(result);
        }
    } catch (error) {
        console.error('Error launching game:', error);
        onError && onError(error);
    }
}

// Export functions for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        launchGame,
        launchSlotGame,
        launchLiveGame,
        launchTableGame,
        launchCustomGame,
        launchGameWithUI
    };
}

// Example usage in browser:
/*
// Launch a slot game
launchSlotGame()
    .then(result => {
        console.log('Slot game launched:', result);
    })
    .catch(error => {
        console.error('Failed to launch slot game:', error);
    });

// Launch with UI feedback
launchGameWithUI(
    {
        agent_code: 'your_agent_code',
        product_code: 1007,
        game_type: 'slot',
        member_account: 'player123',
        nickname: 'Player 123'
    },
    (result) => {
        // Success callback
        alert('Game launched successfully!');
        window.open(result.url, '_blank');
    },
    (error) => {
        // Error callback
        alert('Failed to launch game: ' + error.message);
    }
);
*/ 