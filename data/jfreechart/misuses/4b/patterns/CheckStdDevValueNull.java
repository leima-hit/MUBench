import org.jfree.data.statistics.StatisticalCategoryDataset;

class CheckStdDevValueNull {
  void pattern(StatisticalCategoryDataset dataset, int row, int column) {
    Number n = dataset.getStdDevValue(row, column);
    if (n != null) {
        double valueDelta = n.doubleValue();
        // do something with valueDelta...
    }
  }
}
