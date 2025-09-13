package game.shan.serializeclasses;

import java.util.List;

import game.shan.handlers.TransactionPlayer;

public class TransactionData {
    public String status;
    public String wager_code;
    public List<TransactionPlayer> players;
    public TransactionBanker banker;
    public TransactionAgent agent;
}