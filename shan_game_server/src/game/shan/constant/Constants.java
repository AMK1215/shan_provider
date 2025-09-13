package game.shan.constant;

public final class Constants {
	// Private constructor to prevent instantiation
    private Constants() {
        throw new AssertionError("Cannot instantiate constants class");
    }
    
    //task schedule constants
    public static final int GAME_START_DELAY = 10;
    
  //api uri and endpoints
    public static final String API_URI = "https://luckymillion.pro/api/";
    public static final String API_TRANSITION_ENDPOINT = "transactions";
    public static final String API_TEST_ENDPOINT = "login";
    public static final String API_GETBALANCE_ENDPOINT = "shan/getbalance";
    
    //event names
    public static final String BOT_JOINED = "botJoined"; //send from server
    public static final String COUNTDOWN = "countdown";
    public static final String STARTGAME = "startGame"; //send from client
    public static final String BET_STARTED = "betStarted"; //send from server
    public static final String GAME_STARTED = "gameStarted"; //send from server
    public static final String OWNER = "owner"; //send from server
    public static final String BANKER = "banker"; //send from server
    public static final String START_CURRENT_TURN = "startCurrentTurn"; //send from server
    public static final String PLAYER_HIT = "playerHit"; //send from server
    public static final String PLAYER_WIN = "playerWin"; //send from server
    public static final String PLAYER_LOSE = "playerLose"; //send from server
    public static final String PLAYER_TOTAL_VALUE = "playerTotalValue"; //send from server
    public static final String PLAYER_HAND_CARDS = "playerHandCards"; //send from server
    public static final String HIT = "hit"; //send from client
    public static final String STAND = "stand"; //send from client
    public static final String ROOM_PLAYER_LIST = "roomPlayerList"; //send from server
    public static final String BET = "bet"; //send from client
    public static final String PLAYER_BET = "playerBet"; //send from server
    public static final String PLAYER_SENT_MESSAGE = "playerSentMessage"; //send from client
    public static final String PLAYER_MESSAGE = "playerMessage"; //send from server
    public static final String PLAYER_SENT_EMOJI = "playerSentEmoji"; //send from client
    public static final String PLAYER_EMOJI = "playerEmoji"; //send from server
    public static final String PLEASE_WAIT = "pleaseWait"; //send from server
    public static final String BANK_CATCH = "bankCatch"; //send from client
    public static final String BANK_CATCH_TWO = "bankCatchTwo"; //send from client
    public static final String MATCH_END = "matchEnd"; //send from server
    
    public static final String INDEX = "index";
    
    //shan
    public static final String PLAYER_DRAW = "playerDraw"; //send from server
    public static final String PLAYER_DO = "playerDo"; //send from server
    public static final String DRAW_CARD = "drawCard"; //send from client
    public static final String CATCH_THREE = "catchThree"; //send from server
    public static final String BANKER_CATCH = "bankerCatch"; //send from client
    
    public static final String[] BOT_NAMES = {
    	    "P638482847", "P839383948", "P828582858", "P837582894", "P938582742",
    	    "P949582948", "P929593858", "P938582864", "P938593928", "P682628578",
    	    "P079384717", "P969274728", "P638482847", "P839383942", "P828582856",
    	    "P837582890", "P938582740", "P949582940", "P929593850", "P938582860",
    	    "P938593920", "P682628570", "P079384710", "P969274720"
    	};


    
    //data key
    //card
    public static final String CARD_NAME = "cardName";
    public static final String CARD_VALUE = "cardValue";
    public static final String SUIT = "suit";
    public static final String IS_ACE = "isAce";
    public static final String TOTAL_VALUE = "totalValue";
    public static final String PLAYER_CARD_ARRAY = "playerCardArray";
    public static final String MESSAGE_STRING = "messageString";
    public static final String MODIFIER = "modifier";
    public static final String IS_DO = "isDo";
    
    //user
    //public static final String USER_ID = "userID";
    public static final String USER_NAME = "userName";
    public static final String USER_NAME_ARRAY = "userNameArray";
    public static final String USER_ARRAY = "userArray";
    
    //amount
    public static final String BET_AMOUNT = "betAmount";
    public static final String TOTAL_AMOUNT = "totalAmount";
    public static final String BANK_AMOUNT = "bankAmount";
    public static final String MIN_BET = "minBet";
    public static final String MAX_BET = "maxBet";
    public static final String AMOUNT_CHANGED = "amountChanged";
    public static final String IS_WARNING = "isWarning";
    public static final String IS_BANK_WIN = "isBankWin";

	 // Cards
	 // Clubs
	 public static final String CLUB_ACE = "Club Ace";
	 public static final String CLUB_TWO = "Club 2";
	 public static final String CLUB_THREE = "Club 3";
	 public static final String CLUB_FOUR = "Club 4";
	 public static final String CLUB_FIVE = "Club 5";
	 public static final String CLUB_SIX = "Club 6";
	 public static final String CLUB_SEVEN = "Club 7";
	 public static final String CLUB_EIGHT = "Club 8";
	 public static final String CLUB_NINE = "Club 9";
	 public static final String CLUB_TEN = "Club 10";
	 public static final String CLUB_JACK = "Club Jack";
	 public static final String CLUB_QUEEN = "Club Queen";
	 public static final String CLUB_KING = "Club King";
	
	 // Diamonds
	 public static final String DIAMOND_ACE = "Diamond Ace";
	 public static final String DIAMOND_TWO = "Diamond 2";
	 public static final String DIAMOND_THREE = "Diamond 3";
	 public static final String DIAMOND_FOUR = "Diamond 4";
	 public static final String DIAMOND_FIVE = "Diamond 5";
	 public static final String DIAMOND_SIX = "Diamond 6";
	 public static final String DIAMOND_SEVEN = "Diamond 7";
	 public static final String DIAMOND_EIGHT = "Diamond 8";
	 public static final String DIAMOND_NINE = "Diamond 9";
	 public static final String DIAMOND_TEN = "Diamond 10";
	 public static final String DIAMOND_JACK = "Diamond Jack";
	 public static final String DIAMOND_QUEEN = "Diamond Queen";
	 public static final String DIAMOND_KING = "Diamond King";
	
	 // Hearts
	 public static final String HEART_ACE = "Heart Ace";
	 public static final String HEART_TWO = "Heart 2";
	 public static final String HEART_THREE = "Heart 3";
	 public static final String HEART_FOUR = "Heart 4";
	 public static final String HEART_FIVE = "Heart 5";
	 public static final String HEART_SIX = "Heart 6";
	 public static final String HEART_SEVEN = "Heart 7";
	 public static final String HEART_EIGHT = "Heart 8";
	 public static final String HEART_NINE = "Heart 9";
	 public static final String HEART_TEN = "Heart 10";
	 public static final String HEART_JACK = "Heart Jack";
	 public static final String HEART_QUEEN = "Heart Queen";
	 public static final String HEART_KING = "Heart King";
	
	 // Spades
	 public static final String SPADE_ACE = "Spade Ace";
	 public static final String SPADE_TWO = "Spade 2";
	 public static final String SPADE_THREE = "Spade 3";
	 public static final String SPADE_FOUR = "Spade 4";
	 public static final String SPADE_FIVE = "Spade 5";
	 public static final String SPADE_SIX = "Spade 6";
	 public static final String SPADE_SEVEN = "Spade 7";
	 public static final String SPADE_EIGHT = "Spade 8";
	 public static final String SPADE_NINE = "Spade 9";
	 public static final String SPADE_TEN = "Spade 10";
	 public static final String SPADE_JACK = "Spade Jack";
	 public static final String SPADE_QUEEN = "Spade Queen";
	 public static final String SPADE_KING = "Spade King";

}
