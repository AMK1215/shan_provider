package game.shan.serializeclasses;

public class ResultPlayer{
    public String player_id;
    public String player_name;
    public int win_lose_status;
    public int bet_amount;
    public int amount_changed;
    
    public ResultPlayer(String playerId, String playerName, int winLose, int betAmount, int amountChanged) {
    	this.player_id = playerId;
    	this.player_name = playerName;
    	this.win_lose_status = winLose;
    	this.bet_amount = betAmount;
    	this.amount_changed = amountChanged;
    }
}
