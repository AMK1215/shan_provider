package game.shan.serializeclasses;

public class ResultBanker {
	public String player_id;
    public String player_name;
    public int amount;
    
    public ResultBanker(String playerId, String playerName, int amt) {
    	player_id = playerId;
    	player_name = playerName;
    	amount = amt;
    }
}
