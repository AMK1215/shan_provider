package game.shan.serializeclasses;

public class TransitionReq{
    public ResultBanker banker;
    public ResultPlayer[] players;
    
    public TransitionReq(ResultPlayer[] resPlayers, ResultBanker resBanker) { //temp
		players = resPlayers;
		banker = resBanker;
	}
}
