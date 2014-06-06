import java.sql.*;
import java.util.ArrayList;

public class GestionBDD {
	
	public GestionBDD()
	{
	}
	
	@SuppressWarnings("finally")
	public ArrayList<String> getTests(String testName)
	{
		ArrayList<String> testsAvailable = new ArrayList<String>();
		
		Connection con = null;
		Statement st = null;
		ResultSet rs = null;
		
		try
		{
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			con = DriverManager.getConnection("jdbc:mysql://localhost/projet_web", "root", "");
			st = con.createStatement();
			
			rs = st.executeQuery("SELECT subtest.name FROM subtest WHERE subtest.test_name = '" + testName + "'");
			
			while (rs.next())
			{
				testsAvailable.add(rs.getString("name"));
			}
		}
		catch(Exception e)
		{
			System.err.println("Exception : " + e.getMessage());
		}
		
		finally
		{
			try
			{
				if(rs != null)
					rs.close();
				if(st != null)
					st.close();
				if(con != null)
					con.close();
			}
			catch(Exception e){}
			
			return testsAvailable;
		}
	}
	
	public void addResult(int projectId, String testName, String subtestName, String username, int status, String errors)
	{
		Connection con = null;
		Statement st = null;
		ResultSet rs = null;
		
		try
		{
			Class.forName("com.mysql.jdbc.Driver").newInstance();
			con = DriverManager.getConnection("jdbc:mysql://localhost/projet_web", "root", "");
			st = con.createStatement();
			st.executeUpdate("INSERT INTO users_test (project_id, test_name, subtest_name, username, status, errors) VALUES ('" + projectId + "', '" + testName + "', '" + subtestName + "', '" + username + "', " + Integer.toString(status) + ", '" + errors + "')");
		}
		catch(Exception e)
		{
			System.err.println("Exception : " + e.getMessage());
		}
		
		finally
		{
			try
			{
				if(rs != null)
					rs.close();
				if(st != null)
					st.close();
				if(con != null)
					con.close();
			}
			catch(Exception e){}
		}
	}

}
