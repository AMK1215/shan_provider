package game.shan.serializeclasses;

public class GetBalanceUser {
	public String member_account;
	public String product_code;
	
	public GetBalanceUser (String userID) {
		member_account = userID;
		product_code = "100200";
	}
}
